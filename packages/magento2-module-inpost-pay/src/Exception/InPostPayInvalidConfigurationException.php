<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class InPostPayInvalidConfigurationException extends LocalizedException
{
    private const INVALID_CONFIG_ERROR_MSG = 'Invalid InPost Pay configuration.';
    private const INVALID_CONFIG_ERROR_DETAILS = 'Invalid InPost Pay configuration. Details: %1';

    public function __construct(
        Phrase $phrase = null,
        Exception $cause = null,
        $code = 0
    ) {
        if ($phrase !== null) {
            $phrase = new Phrase(self::INVALID_CONFIG_ERROR_DETAILS, [$phrase->render()]);
        } else {
            $phrase = new Phrase(self::INVALID_CONFIG_ERROR_MSG);
        }

        parent::__construct($phrase, $cause, $code);
    }
}
