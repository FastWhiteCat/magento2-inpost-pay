<?php
namespace InPost\InPostPay\Plugin;

use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateCheckoutAgreementsVersionPlugin
{
    /**
     * @param CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository,
        private readonly CacheInterface $cache
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
            $data['agreement_id'] = $agreement->getAgreementId();
            $data['version'] = uniqid();

            $this->checkoutAgreementsVersionRepository->save($data);
            $this->cache->clean([TermsAndConditionsCacheType::TYPE_IDENTIFIER]);
        }
    }
}
