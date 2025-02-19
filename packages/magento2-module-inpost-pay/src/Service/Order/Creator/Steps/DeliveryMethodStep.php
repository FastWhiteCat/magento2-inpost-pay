<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Enum\InPostDeliveryType;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Observer\Quote\UpdateInPostBasketEventObserver;
use InPost\InPostPay\Provider\Config\ShipmentMappingConfigProvider;
use InPost\InPostPay\Service\Cart\CartService;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class DeliveryMethodStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ShipmentMappingConfigProvider $shipmentMappingConfigProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @param Quote $quote
     * @param OrderInterface $inPostOrder
     * @return void
     * @throws InPostPayInternalException
     */
    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        if ($inPostOrder->getDelivery()->getDeliveryType() === InPostDeliveryType::DIGITAL->value) {
            return;
        }

        $deliveryType = $inPostOrder->getDelivery()->getDeliveryType();
        if ($inPostOrder->getDelivery()->getDeliveryCodes()) {
            $deliveryOption = implode('', $inPostOrder->getDelivery()->getDeliveryCodes());
        } else {
            $deliveryOption = ShipmentMappingConfigProvider::OPTION_STANDARD;
        }

        $deliveryMethod = $this->shipmentMappingConfigProvider->getCarrierMethodCodeForOptions(
            $deliveryType,
            $deliveryOption
        );

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($deliveryMethod);
        $quote->setData(CartService::ALLOW_INPOST_PAY_QUOTE_REMOTE_ACCESS, true);
        $quote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
        $this->cartRepository->save($quote);
        $quoteId = (int)(is_scalar($quote->getId()) ? $quote->getId() : null);
        $this->createLog(
            sprintf('Delivery method %s has been applied to quote ID: %s', $deliveryMethod, $quoteId)
        );
    }
}
