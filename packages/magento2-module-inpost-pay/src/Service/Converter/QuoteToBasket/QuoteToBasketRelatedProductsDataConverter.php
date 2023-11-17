<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use Magento\Quote\Model\Quote;

class QuoteToBasketRelatedProductsDataConverter implements QuoteToBasketDataConverterInterface
{
    public function convert(Quote $quote): array
    {
        //TODO:: related/crosssell converter
        return [];
    }
}
