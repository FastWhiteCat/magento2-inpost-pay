<?php
declare(strict_types=1);

namespace InPost\InPostPayHyvaCheckout\Plugin;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;

class HidePaymentMethodPlugin
{
    private const METHOD_CODE_TO_HIDE = 'inpost_pay';

    /**
     * @param MethodList $subject
     * @param MethodInterface[] $availableMethods
     * @param CartInterface|null $quote
     * @return MethodInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        array $availableMethods,
        ?CartInterface $quote = null
    ): array {
        foreach ($availableMethods as $key => $method) {
            if ($method->getCode() === self::METHOD_CODE_TO_HIDE) {
                unset($availableMethods[$key]);
            }
        }

        return array_values($availableMethods);
    }
}
