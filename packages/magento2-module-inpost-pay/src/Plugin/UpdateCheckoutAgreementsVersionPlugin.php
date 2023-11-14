<?php
namespace InPost\InPostPay\Plugin;

use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateCheckoutAgreementsVersionPlugin
{
    /**
     * @param CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     */
    public function __construct(
        private readonly CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
    ) {
    }

    /**
     * @param AgreementInterface $agreement
     * @return void
     */
    public function beforeSave(
        AgreementInterface $agreement
    ):void {
        try {
            $originAgreement = $this->checkoutAgreementsRepository->get($agreement->getAgreementId());
            if ($originAgreement->getContent() !== $agreement->getContent() ||
                $originAgreement->getName() !== $agreement->getName()
            ) {
                $agreement->setData('changed_version', true);
            }
        } catch (NoSuchEntityException $exception) {
            $agreement->setData('changed_version', true);
        }
    }

    /**
     * @param AgreementInterface $agreement
     * @return void
     */
    public function afterSave(
        AgreementInterface $agreement
    ):void {
        if ($agreement->getData('changed_version')) {
            $checkoutAgreementVersion = $this->checkoutAgreementsVersionRepository
                ->getCheckoutAgreementVersion((int)$agreement->getAgreementId());
            $data['agreement_id'] = $agreement->getAgreementId();
            $data['version'] = 1;
            if ($checkoutAgreementVersion) {
                $data['version'] = $checkoutAgreementVersion + 1;
            }

            $this->checkoutAgreementsVersionRepository->save($data);
        }
    }
}
