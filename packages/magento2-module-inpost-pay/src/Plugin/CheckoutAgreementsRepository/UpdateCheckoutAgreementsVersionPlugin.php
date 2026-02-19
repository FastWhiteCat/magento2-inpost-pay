<?php
namespace InPost\InPostPay\Plugin\CheckoutAgreementsRepository;

use InPost\InPostPay\Api\CheckoutAgreementsVersionRepositoryInterface;
use InPost\InPostPay\Model\Cache\TermsAndConditions\Type as TermsAndConditionsCacheType;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface;
use Magento\CheckoutAgreements\Api\Data\AgreementInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class UpdateCheckoutAgreementsVersionPlugin
{
    /**
     * @param CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository
     * @param StoreManagerInterface $storeManager
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly CheckoutAgreementsVersionRepositoryInterface $checkoutAgreementsVersionRepository,
        private readonly StoreManagerInterface $storeManager,
        private readonly CacheInterface $cache
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
            $data['agreement_id'] = $agreement->getAgreementId();
            $data['version'] = uniqid();

            $this->checkoutAgreementsVersionRepository->save($data);
            $cacheIdentifiers = [TermsAndConditionsCacheType::TYPE_IDENTIFIER];

            foreach ($this->storeManager->getStores(true) as $store) {
                $cacheIdentifiers[] = sprintf(
                    '%s_%s',
                    TermsAndConditionsCacheType::TYPE_IDENTIFIER,
                    (int)$store->getId()
                );
            }

            $this->cache->clean($cacheIdentifiers);
        }
    }
}
