<?php
namespace InPost\InPostPay\Plugin\CheckoutAgreementsRepository;

use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateCheckoutAgreementsVersionPlugin
{
    /**
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     */
    public function __construct(
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
    ) {
    }

    /**
     * @param CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param AgreementInterface $agreement
     * @return void
     */
    public function beforeSave(
        CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        AgreementInterface $agreement
    ):void {
        try {
            $originAgreement = $checkoutAgreementsRepository->get($agreement->getAgreementId());
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
