<?php
declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayQuoteInterface
{
    public const TABLE_NAME = 'inpost_pay_quote';
    public const ENTITY_NAME = 'inpost_pay_quote';
    public const INPOST_PAY_QUOTE_ID = 'inpost_pay_quote_id';
    public const QUOTE_ID = 'quote_id';
    public const BASKET_ID = 'basket_id';
    public const BASKET_BINDING_API_KEY = 'basket_binding_api_key';
    public const INPOST_BASKET_ID = 'inpost_basket_id';
    public const STATUS = 'status';
    public const PHONE_NUMBER = 'phone_number';
    public const PHONE = 'phone';
    public const COUNTRY_PREFIX = 'country_prefix';
    public const MASKED_PHONE_NUMBER = 'masked_phone_number';
    public const BROWSER_TRUSTED = 'browser_trusted';
    public const BROWSER_ID = 'browser_id';
    public const NAME = 'name';
    public const SURNAME = 'surname';
    public const CART_VERSION = 'cart_version';
    public const GA_CLIENT_ID = 'ga_client_id';
    public const FBCLID = 'fbclid';
    public const GCLID = 'gclid';
    public const SESSION_COOKIE = 'session_cookie';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public function getInPostPayQuoteId(): ?int;
    public function setInPostPayQuoteId(int $inPostPayQuoteId): InPostPayQuoteInterface;

    public function getQuoteId(): int;
    public function setQuoteId(int $quoteId): InPostPayQuoteInterface;

    public function getBasketId(): string;
    public function setBasketId(string $basketId): InPostPayQuoteInterface;

    public function getBasketBindingApiKey(): ?string;
    public function setBasketBindingApiKey(?string $basketBindingApiKey): InPostPayQuoteInterface;

    public function getInpostBasketId(): ?string;
    public function setInpostBasketId(string $inpostBasketId): InPostPayQuoteInterface;

    public function getStatus(): string;
    public function setStatus(string $status): InPostPayQuoteInterface;

    public function getPhone(): ?string;
    public function setPhone(string $phone): InPostPayQuoteInterface;

    public function getCountryPrefix(): ?string;
    public function setCountryPrefix(string $countryPrefix): InPostPayQuoteInterface;

    public function getMaskedPhoneNumber(): ?string;
    public function setMaskedPhoneNumber(string $maskedPhoneNumber): InPostPayQuoteInterface;

    public function getBrowserTrusted(): bool;
    public function setBrowserTrusted(bool $browserTrusted): InPostPayQuoteInterface;

    public function getBrowserId(): ?string;
    public function setBrowserId(string $browserId): InPostPayQuoteInterface;

    public function getName(): ?string;
    public function setName(string $name): InPostPayQuoteInterface;

    public function getSurname(): ?string;
    public function setSurname(string $surname): InPostPayQuoteInterface;

    public function getCartVersion(): string;
    public function setCartVersion(string $cartVersion): InPostPayQuoteInterface;
    public function getSessionCookie(): ?string;
    public function setSessionCookie(?string $sessionCookie): InPostPayQuoteInterface;

    public function getGaClientId(): ?string;
    public function setGaClientId(?string $gaClientId): InPostPayQuoteInterface;

    public function getFbclid(): ?string;
    public function setFbclid(?string $fbclid): InPostPayQuoteInterface;

    public function getGclid(): ?string;
    public function setGclid(?string $gclid): InPostPayQuoteInterface;

    public function getCreatedAt(): string;
    public function getUpdatedAt(): string;
}
