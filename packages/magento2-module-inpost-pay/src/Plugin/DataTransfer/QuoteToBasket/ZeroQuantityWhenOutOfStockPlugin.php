<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasket\QuoteToBasketProductsDataTransfer;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class ZeroQuantityWhenOutOfStockPlugin
{
    public function __construct(
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param QuoteToBasketProductsDataTransfer $subject
     * @param $result
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer( //@phpstan-ignore-line
        QuoteToBasketProductsDataTransfer $subject,
        $result,
        Quote $quote,
        BasketInterface $basket
    ): void {
        foreach ($basket->getProducts() as $inPostPayProduct) {
            $availableQuantity = $inPostPayProduct->getQuantity()->getAvailableQuantity();
            $basketQuantity = $inPostPayProduct->getQuantity()->getQuantity();

            if ($availableQuantity <= 0 && $basketQuantity > 0) {
                $inPostPayProduct->getQuantity()->setQuantity(0.00);

                $warningMsg = __(
                    'Item "%1" is no longer available in requested quantity: %2. Currently available: %3',
                    $inPostPayProduct->getProductName(),
                    $basketQuantity,
                    $availableQuantity
                )->render();
                $this->addBasketNotice((string)$basket->getBasketId(), $warningMsg);

                $this->logger->warning(
                    sprintf(
                        'Basket quantity has been changed reduced to 0 because product %s is no longer available.',
                        $inPostPayProduct->getProductId()
                    )
                );
            }
        }
    }

    private function addBasketNotice(string $basketId, string $message): void
    {
        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            $message
        );
    }
}
