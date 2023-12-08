<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\GetGuestMobileLinkInterface;
use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterface;
use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterfaceFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;

class GetGuestMobileLink implements GetGuestMobileLinkInterface
{
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly GetMobileLink $getMobileLink,
    ) {
    }

    public function execute(string $cartId): MobileLinkInterface
    {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);

        return $this->getMobileLink->execute((int)$quoteId);
    }
}
