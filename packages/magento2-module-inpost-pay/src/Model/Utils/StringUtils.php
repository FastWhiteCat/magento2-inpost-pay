<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Utils;

use Magento\Framework\Stdlib\StringUtils as CoreStringUtils;

class StringUtils
{
    /**
     * @param CoreStringUtils $stringUtils
     */
    public function __construct(
        private readonly CoreStringUtils $stringUtils
    ) {
    }

    /**
     * @param string $text
     * @param int|null $maxLength
     *
     * @return string
     */
    public function cleanUpString(string $text, ?int $maxLength = null): string
    {
        // Remove inline styles and &nbsp; tags
        $text = (string)preg_replace('/(<style([^<])*<\/style>|&nbsp;)/i', '', $text);
        // Strip html tags and replace them with single space
        $text = (string)preg_replace('#<[^>]+>#', ' ', $text);
        $text = (string)preg_replace('!\s+!', ' ', $text);
        $text = trim($text);
        $text = $this->stringUtils->cleanString($text);

        if ($maxLength) {
            if ($this->stringUtils->strlen($text) > $maxLength) {
                $text = $this->stringUtils->substr($text, 0, $maxLength) . '...';
            }
        }

        return $text;
    }
}
