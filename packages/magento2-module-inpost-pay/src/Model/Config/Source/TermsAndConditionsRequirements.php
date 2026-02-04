<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TermsAndConditionsRequirements implements OptionSourceInterface
{
    public const ALWAYS = 'REQUIRED_ALWAYS';
    public const ONLY_IN_NEW_VERSION = 'REQUIRED_ONCE';
    public const OPTIONAL = 'OPTIONAL';
    public const ADDITIONAL_LINK = 'ADDITIONAL_LINK';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['label' => self::ALWAYS, 'value' => self::ALWAYS],
            ['label' => self::ONLY_IN_NEW_VERSION, 'value' => self::ONLY_IN_NEW_VERSION],
            ['label' => self::OPTIONAL, 'value' => self::OPTIONAL],
            ['label' => self::ADDITIONAL_LINK, 'value' => self::ADDITIONAL_LINK]
        ];
    }
}
