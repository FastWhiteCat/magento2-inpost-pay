<?php
declare(strict_types=1);

namespace InPost\InPostPay\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AddInitializationDataToCheckoutAgreementStore implements DataPatchInterface
{
    private const CHECKOUT_AGREEMENT_VERSION_TABLE = 'inpost_pay_checkout_agreement_version';

    /**
     * AddProductSendLockerAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CheckoutAgreementsListInterface $checkoutAgreementsList
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CheckoutAgreementsListInterface $checkoutAgreementsList,
        private readonly SearchCriteriaInterface $searchCriteria
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): AddInitializationDataToCheckoutAgreementStore
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $searchCriteria = $this->searchCriteria;
        $agreementList = $this->checkoutAgreementsList->getList($searchCriteria);

        $data = [];
        foreach ($agreementList as $agreement) {
            $data[] = [
                'agreement_id' => $agreement->getAgreementId(),
                'version' => 1
            ];
        }

        if (!empty($data)) {
            $this->moduleDataSetup->getConnection()->insertArray(
                self::CHECKOUT_AGREEMENT_VERSION_TABLE,
                ['agreement_id', 'version'],
                $data
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
