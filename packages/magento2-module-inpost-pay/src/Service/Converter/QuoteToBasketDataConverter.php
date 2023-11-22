<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use InPost\InPostPay\Api\ApiConnector\IziApi\Basket\BasketFieldInterface as Basket;
use Psr\Log\LoggerInterface;

class QuoteToBasketDataConverter implements QuoteToBasketDataConverterInterface
{
    /**
     * @var QuoteToBasketDataConverterInterface[]
     */
    private array $converters = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        array $converters = []
    ) {
        $this->initConverters($converters);
    }

    public function convert(Quote $quote): array
    {
        $basketData = [
            Basket::BROWSER_ID => $this->getBrowserIdFromCart($quote),
            Basket::BASKET_ID => uniqid()
        ];

        foreach ($this->converters as $converterKey => $converter) {
            $basketData[$converterKey] = $converter->convert($quote);
        }

        return $basketData;
    }

    private function getBrowserIdFromCart(Quote $quote): string
    {
        //TODO::fill after INPAY-28 is implemented from inpost_pay_quote.browser_id field

        return '2d387d15-d4fe-43f8-85dc-32d46cfc3b53';
    }

    /**
     * @param array $converters
     * @return void
     * @throws InvalidArgumentException
     */
    private function initConverters(array $converters): void
    {
        foreach ($converters as $converterKey => $converter) {
            if ($converter instanceof QuoteToBasketDataConverterInterface) {
                $this->converters[$converterKey] = $converter;
            } else {
                $errorMsg = sprintf('Quote to Basket converter: %s is not valid.', $converterKey);
                $this->logger->critical($errorMsg);

                throw new InvalidArgumentException($errorMsg);
            }
        }
    }
}
