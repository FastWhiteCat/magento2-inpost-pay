<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Service\DataTransfer\QuoteToBasketDataTransfer;

use InPost\InPostPay\Api\Data\InPostPayBasketNoticeInterface;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Registry\Quote\DigitalQuoteAllowRegistry;
use InPost\InPostPay\Service\CreateBasketNotice;
use InPost\InPostPay\Service\DataTransfer\QuoteToBasketDataTransfer;
use InPost\InPostPay\Validator\DigitalQuoteValidator;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class RegisterIfDigitalQuoteIsAllowedForMagentoConfigPlugin
{
    /**
     * @param DigitalQuoteValidator $digitalQuoteValidator
     * @param DigitalQuoteAllowRegistry $digitalQuoteAllowRegistry
     * @param CreateBasketNotice $createBasketNotice
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly DigitalQuoteValidator $digitalQuoteValidator,
        private readonly DigitalQuoteAllowRegistry $digitalQuoteAllowRegistry,
        private readonly CreateBasketNotice $createBasketNotice,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param QuoteToBasketDataTransfer $subject
     * @param Quote $quote
     * @param BasketInterface $basket
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeTransfer(QuoteToBasketDataTransfer $subject, Quote $quote, BasketInterface $basket): void
    {
        if (!$this->digitalQuoteValidator->isDigitalQuoteAllowed($quote)) {
            $this->digitalQuoteAllowRegistry->registerIfCurrentlyProcessedDigitalQuoteIsAllowed(false);
            $this->setBasketNoticeForGuestUnavailableDigitalProducts((string)$basket->getBasketId());
        }
    }

    /**
     * @param QuoteToBasketDataTransfer $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer(QuoteToBasketDataTransfer $subject): void
    {
        $this->digitalQuoteAllowRegistry->resetRegistry();
    }

    private function setBasketNoticeForGuestUnavailableDigitalProducts(string $basketId): void
    {
        $this->logger->error(
            sprintf(
                'Basket %s contains digital products and Magento config does not allow guest orders.',
                $basketId
            )
        );

        $this->createBasketNotice->execute(
            $basketId,
            InPostPayBasketNoticeInterface::ATTENTION,
            __(
                'Cart contains digital products that cannot be ordered as a not logged in user.'
                . ' Please create account in Merchants website in order to complete this purchase.'
            )->render()
        );
    }
}
