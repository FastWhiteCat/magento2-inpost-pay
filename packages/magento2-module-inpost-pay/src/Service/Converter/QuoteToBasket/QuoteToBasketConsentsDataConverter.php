<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Provider\ConsentsProvider;
use Magento\Quote\Model\Quote;

class QuoteToBasketConsentsDataConverter implements QuoteToBasketDataConverterInterface
{
    public function __construct(
        private readonly ConsentsProvider $consentsProvider
    ) {
    }

    public function convert(Quote $quote): array
    {
        return $this->consentsProvider->getConsents();
    }
}
