<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Quote;

use InPost\InPostPay\Provider\Product\Attribute\InPostPayProductAttributesProvider;
use Magento\Quote\Model\Quote\Config;

class AddAdditionalQuoteProductAttributesPlugin
{
    public function __construct(
        private readonly InPostPayProductAttributesProvider $inPostPayProductAttributesProvider
    ) {
    }

    /**
     * @param Config $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(Config $subject, array $result): array
    {
        $result = array_merge($result, $this->inPostPayProductAttributesProvider->getProductAttributeCodes());

        return array_unique($result);
    }
}
