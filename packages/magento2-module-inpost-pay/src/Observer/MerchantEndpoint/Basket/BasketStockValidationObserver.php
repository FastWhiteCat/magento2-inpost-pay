<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Basket;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\CreateBasketNotice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class BasketStockValidationObserver implements ObserverInterface
{
    public function __construct(
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $basket = $event->getData(BasketConfirmationInterface::BASKET);
        if ($basket instanceof BasketInterface) {
            $errors = $this->prepareBasketStockErrors($basket);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->createBasketNotice->execute(
                        $basket->getBasketId(),
                        InPostPayBasketNoticeInterface::ATTENTION,
                        $error
                    );
                }
            }
        }
    }

    private function prepareBasketStockErrors(BasketInterface $basket): array
    {
        $errors = [];
        foreach ($basket->getProducts() as $product) {
            $quantity = $product->getQuantity();
            $basketQuantity = (float)$quantity->getQuantity();
            $availableQuantity = $quantity->getAvailableQuantity();
            if ($basketQuantity > $availableQuantity) {
                $error = __(
                    'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                    $product->getProductName(),
                    $basketQuantity,
                    $availableQuantity
                )->render();

                $this->logger->warning(
                    sprintf('Basket %s Stock Validation Warning: %s', (string)$basket->getBasketId(), $error)
                );

                $errors[] = $error;
            }
        }

        return $errors;
    }
}
