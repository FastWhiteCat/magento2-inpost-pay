<?php

declare(strict_types=1);

namespace InPost\InPostPay\Exception;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class InPostPayInvalidConfigurationException extends LocalizedException
{
    private const INVALID_CONFIG_ERROR_MSG = 'Invalid InPost Pay configuration.';
    private const INVALID_CONFIG_ERROR_DETAILS = ' Details: %1';

    public function __construct(
        Phrase $phrase = null,
        Exception $cause = null,
        $code = 0
    ) {
        $errorMsg = self::INVALID_CONFIG_ERROR_MSG;
        if ($phrase !== null) {
            $errorMsg .= self::INVALID_CONFIG_ERROR_DETAILS;
            $phrase = new Phrase($errorMsg, [$phrase->render()]);
        } else {
            $phrase = new Phrase($errorMsg);
        }

        parent::__construct($phrase, $cause, $code);
    }
}
