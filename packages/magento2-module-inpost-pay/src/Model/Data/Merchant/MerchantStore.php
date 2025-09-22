<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Data\Merchant;

use InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStoreInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;

class MerchantStore extends DataObject implements MerchantStoreInterface, ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getUrl(): string
    {
        $url = $this->getData(self::URL);

        return is_scalar($url) ? (string)$url : '';
    }

    /**
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->setData(self::URL, $url);
    }

    /**
     * @return \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface[]
     */
    public function getCookies(): array
    {
        $cookies = $this->getData(self::COOKIES);

        return is_array($cookies) ? $cookies : [];
    }

    /**
     * @param \InPost\InPostPay\Api\Data\Merchant\Basket\MerchantStore\CookieInterface[] $cookies
     * @return void
     */
    public function setCookies(array $cookies): void
    {
        $this->setData(self::COOKIES, $cookies);
    }
}
