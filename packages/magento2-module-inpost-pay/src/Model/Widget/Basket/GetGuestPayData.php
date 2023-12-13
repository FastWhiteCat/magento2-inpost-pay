<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\GetGuestPayDataInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class GetGuestPayData implements GetGuestPayDataInterface
{
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly GetPayData $getPayData,
    ) {
    }

    public function execute(
        string $cartId,
        string $bindingPlace,
        string $browser,
        ?string $prefix = null,
        ?string $phoneNumber = null
    ): array {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->getPayData->execute(
            (int)$quoteId,
            $bindingPlace,
            $browser,
            $prefix,
            $phoneNumber
        );
    }
}
