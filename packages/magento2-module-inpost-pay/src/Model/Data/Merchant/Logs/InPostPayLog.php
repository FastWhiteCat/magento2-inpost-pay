<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Logs;

use InPost\InPostPay\Api\Data\Merchant\Logs\InPostPayLogInterface;
use Magento\Framework\DataObject;

class InPostPayLog extends DataObject implements InPostPayLogInterface
{
    /**
     * @return string
     */
    public function getLogLevel(): string
    {
        $logLevel = $this->getData(self::LOG_LEVEL);

        return is_scalar($logLevel) ? (string)$logLevel : '';
    }

    /**
     * @param string $logLevel
     * @return void
     */
    public function setLogLevel(string $logLevel): void
    {
        $this->setData(self::LOG_LEVEL, $logLevel);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        $content = $this->getData(self::CONTENT);

        return is_scalar($content) ? (string)$content : '';
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->setData(self::CONTENT, $content);
    }
}
