<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Model\Data\Basket\Product;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;

class LimitProductAttributeNameAndValueCharactersPlugin
{
    public const MAX_LENGTH = 255;

    /**
     * @param ProductAttributeInterface $subject
     * @param string $attributeValue
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetAttributeValue(ProductAttributeInterface $subject, string $attributeValue): array
    {
        return [$this->limitString($attributeValue, self::MAX_LENGTH)];
    }

    /**
     * @param ProductAttributeInterface $subject
     * @param string $attributeName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetAttributeName(ProductAttributeInterface $subject, string $attributeName): array
    {
        return [$this->limitString($attributeName, self::MAX_LENGTH)];
    }

    /**
     * @param string $value
     * @param int $limit
     * @param string $suffix
     * @return string
     */
    private function limitString(string $value, int $limit, string $suffix = '...'): string
    {
        $suffixLength = mb_strlen($suffix);

        if (mb_strlen($value) > $limit) {
            return mb_substr($value, 0, $limit - $suffixLength - 1) . $suffix;
        }

        return $value;
    }
}
