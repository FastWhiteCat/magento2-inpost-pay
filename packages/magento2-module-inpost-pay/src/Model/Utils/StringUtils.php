<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Utils;

use InPost\InPostPay\Provider\Config\IziApiConfigProvider;
use InPost\InPostPay\Provider\Description\ForbiddenSignsAndPhrases;
use Magento\Framework\Stdlib\StringUtils as CoreStringUtils;

class StringUtils
{
    /**
     * @param CoreStringUtils $stringUtils
     * @param ForbiddenSignsAndPhrases $forbiddenSignsAndPhrases
     * @param IziApiConfigProvider $iziApiConfigProvider
     */
    public function __construct(
        private readonly CoreStringUtils $stringUtils,
        private readonly ForbiddenSignsAndPhrases $forbiddenSignsAndPhrases,
        private readonly IziApiConfigProvider $iziApiConfigProvider
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
        if (!$this->iziApiConfigProvider->isProductAttributesHTMLAndSpecialCharactersCleaningEnabled()) {
            return $text;
        }

        $cleanText = $this->prepareTextToClean($text);
        $cleanText = $this->removeForbiddenSignsAndPhrasesFromText($cleanText);
        $cleanText = $this->removeHtmlFromText($cleanText);
        $cleanText = $this->removeNonUTF8Characters($cleanText);

        if ($maxLength) {
            $cleanText = $this->cutTextUpToLength($cleanText, $maxLength);
        }

        return $cleanText;
    }

    private function prepareTextToClean(string $text): string
    {
        $text = (string)str_replace('&amp;', '&', $text);
        $text = (string)str_replace('&lt;', '<', $text);
        $text = (string)str_replace('&gt;', '>', $text);

        return $text;
    }

    private function removeForbiddenSignsAndPhrasesFromText(string $text): string
    {
        $forbiddenSignsAndPhrases = $this->forbiddenSignsAndPhrases->getList();
        if (!empty($forbiddenSignsAndPhrases)) {
            $forbiddenSignsAndPhrasesPattern = sprintf('/(%s)/i', implode('|', $forbiddenSignsAndPhrases));
            $text = (string)preg_replace($forbiddenSignsAndPhrasesPattern, '', $text);
        }

        return $text;
    }

    private function removeHtmlFromText(string $text): string
    {
        // Remove inline styles and &nbsp; tags
        $text = (string)preg_replace('/(<style([^<])*<\/style>|&nbsp;)/i', '', $text);
        // Strip html tags and replace them with single space
        $text = (string)preg_replace('#<[^>]+>#', ' ', $text);
        $text = (string)preg_replace('!\s+!', ' ', $text);

        return trim($text);
    }

    private function removeNonUTF8Characters(string $text): string
    {
        return $this->stringUtils->cleanString($text);
    }

    private function cutTextUpToLength(string $text, int $maxLength): string
    {
        if ($this->stringUtils->strlen($text) > $maxLength) {
            $text = $this->stringUtils->substr($text, 0, $maxLength) . '...';
        }

        return $text;
    }
}
