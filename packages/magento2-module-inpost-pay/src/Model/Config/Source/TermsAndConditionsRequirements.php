<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TermsAndConditionsRequirements implements OptionSourceInterface
{
    public const ALWAYES = 'REQUIRED_ALWAYS';
    public const ONLY_IN_NEW_VERSION = 'REQUIRED_ONCE';
    public const OPTIONAL = 'OPTIONAL';

    public const LABELS = [
        self::ALWAYES => 'always',
        self::ONLY_IN_NEW_VERSION => 'only in new version',
        self::OPTIONAL => 'optional'
    ];

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => self::ALWAYES, 'value' => __(self::LABELS[self::ALWAYES])],
            ['label' => self::ONLY_IN_NEW_VERSION, 'value' => __(self::LABELS[self::ONLY_IN_NEW_VERSION])],
            ['label' => self::OPTIONAL, 'value' => __(self::LABELS[self::OPTIONAL])]
        ];
    }
}
