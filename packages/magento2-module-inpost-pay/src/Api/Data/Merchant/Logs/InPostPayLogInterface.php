<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data\Merchant\Logs;

interface InPostPayLogInterface
{
    public const LOG_LEVEL = 'log_level';
    public const CONTENT = 'content';

    /**
     * @return string
     */
    public function getLogLevel(): string;

    /**
     * @param string $logLevel
     * @return void
     */
    public function setLogLevel(string $logLevel): void;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void;
}
