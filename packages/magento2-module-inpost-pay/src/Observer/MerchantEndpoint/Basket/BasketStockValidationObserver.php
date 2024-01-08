<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\MerchantEndpoint\Basket;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketConfirmationInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class BasketStockValidationObserver implements ObserverInterface
{
    public function __construct(
        private readonly NoticeInterfaceFactory $noticeFactory,
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
                $summary = $basket->getSummary();
                if ($notice = $summary->getBasketNotice()) {
                    $noticeErrors = explode(PHP_EOL, $notice->getDescription());
                    $errors = array_merge($noticeErrors, $errors);
                    $notice->setDescription(implode(PHP_EOL, $errors));
                } else {
                    $notice = $this->prepareStockAttentionNotice(implode(PHP_EOL, $errors));
                }

                $summary->setBasketNotice($notice);
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

    private function prepareStockAttentionNotice(string $description): NoticeInterface
    {
        /** @var NoticeInterface $notice */
        $notice = $this->noticeFactory->create();
        $notice->setType(NoticeInterface::ATTENTION);
        $notice->setDescription($description);

        return $notice;
    }
}
