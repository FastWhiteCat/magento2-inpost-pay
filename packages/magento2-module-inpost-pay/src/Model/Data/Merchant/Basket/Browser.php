<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant\Basket;

use InPost\InPostPay\Api\Data\Merchant\Basket\BrowserInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class Browser extends DataObject implements BrowserInterface, ExtensibleDataInterface
{
    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getBrowserTrusted(): bool
    {
        $browserTrusted = $this->getData(self::BROWSER_TRUSTED);

        return (is_bool($browserTrusted)) ? $browserTrusted : false;
    }

    /**
     * @param bool $browserTrusted
     * @return void
     */
    public function setBrowserTrusted(bool $browserTrusted): void
    {
        $this->setData(self::BROWSER_TRUSTED, $browserTrusted);
    }

    /**
     * @return string
     */
    public function getBrowserId(): string
    {
        $browserId = $this->getData(self::BROWSER_ID);

        return (is_scalar($browserId)) ? (string)$browserId : '';
    }

    /**
     * @param string $browserId
     * @return void
     */
    public function setBrowserId(string $browserId): void
    {
        $this->setData(self::BROWSER_ID, $browserId);
    }
}
