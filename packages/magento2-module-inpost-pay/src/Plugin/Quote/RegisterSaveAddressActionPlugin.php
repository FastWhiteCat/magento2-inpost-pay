<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Quote;

use InPost\InPostPay\Registry\SaveQuoteAddressActionRegistry;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;

class RegisterSaveAddressActionPlugin
{
    /**
     * @param SaveQuoteAddressActionRegistry $saveQuoteAddressActionRegistry
     */
    public function __construct(
        private readonly SaveQuoteAddressActionRegistry $saveQuoteAddressActionRegistry
    ) {
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(ShippingInformationManagementInterface $subject,): void
    {
        $this->saveQuoteAddressActionRegistry->registerSaveQuoteAddressAction();
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param PaymentDetailsInterface $result
     * @return PaymentDetailsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        PaymentDetailsInterface $result
    ): PaymentDetailsInterface {
        $this->saveQuoteAddressActionRegistry->resetRegistry();

        return $result;
    }
}
