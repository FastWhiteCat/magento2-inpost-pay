<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Source\Store;

use Magento\Framework\Data\OptionSourceInterface;

class BestsellerProductPriority implements OptionSourceInterface
{
    public const MIN_PRIORITY = 1;
    public const MAX_PRIORITY = 5;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        for ($i = self::MIN_PRIORITY; $i <= self::MAX_PRIORITY; $i++) {
            $options[] = [
                'value' => $i,
                'label' => (string)$i
            ];
        }

        return $options;
    }
}
