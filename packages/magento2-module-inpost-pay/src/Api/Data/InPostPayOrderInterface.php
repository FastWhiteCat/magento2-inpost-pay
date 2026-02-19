<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;

interface InPostPayOrderInterface
{
    public const TABLE_NAME = 'inpost_pay_order';
    public const ENTITY_NAME = 'inpost_pay_order';
    public const INPOST_PAY_ORDER_ID = 'inpost_pay_order_id';
    public const ORDER_ID = 'order_id';
    public const BASKET_ID = 'basket_id';
    public const BASKET_BINDING_API_KEY = 'basket_binding_api_key';
    public const PAYMENT_TYPE = 'payment_type';
    public const LOCKER_ID = 'locker_id';
    public const ORDER_STATUS = 'order_status';
    public const COUNTRY_PREFIX = 'country_prefix';
    public const PHONE = 'phone';
    public const PHONE_NUMBER = 'phone';
    public const COURIER_NOTE = 'courier_note';
    public const GA_CLIENT_ID = 'ga_client_id';
    public const CLIENT_ID = 'client_id';
    public const FBCLID = 'fbclid';
    public const GCLID = 'gclid';
    public const SERIALIZED_ANALYTICS_DATA = 'serialized_analytics_data';
    public const ANALYTICS_SENT_AT = 'analytics_sent_at';
    public const DELIVERY_OPTIONS = 'delivery_options';
    public const ACCEPTED_CONSENTS = 'accepted_consents';
    public const INPOST_PAY_ACCOUNT_EMAIL = 'inpost_pay_account_email';
    public const ORDER_WITH_INVOICE = 'order_with_invoice';
    public const INPOST_PAY_INVOICE_EMAIL = 'inpost_pay_invoice_email';
    public const DELIVERY_EMAIL = 'delivery_email';
    public const DIGITAL_DELIVERY_EMAIL = 'digital_delivery_email';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const IS_TIMED_OUT = 'is_timed_out';
    public const IS_TIME_OUT_HANDLED = 'is_time_out_handled';

    public function getInPostPayOrderId(): ?int;
    public function setInPostPayOrderId(int $inPostPayOrderId): InPostPayOrderInterface;

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getOrderId(): int;
    public function setOrderId(int $orderId): InPostPayOrderInterface;
    public function getBasketId(): ?string;
    public function setBasketId(?string $basketId): InPostPayOrderInterface;
    public function getBasketBindingApiKey(): ?string;
    public function setBasketBindingApiKey(?string $basketBindingApiKey): InPostPayOrderInterface;
    public function getPaymentType(): ?string;
    public function setPaymentType(?string $paymentType): InPostPayOrderInterface;
    public function getLockerId(): ?string;
    public function setLockerId(string $lockerId): InPostPayOrderInterface;
    public function getOrderStatus(): ?string;
    public function setOrderStatus(string $orderStatus): InPostPayOrderInterface;
    public function getPhone(): ?string;
    public function setPhone(string $phone): InPostPayOrderInterface;
    public function getCountryPrefix(): ?string;
    public function setCountryPrefix(string $countryPrefix): InPostPayOrderInterface;
    public function getPhoneNumber(): PhoneNumberInterface;
    public function getDeliveryOptions(): array;
    public function setDeliveryOptions(array $deliveryOptions): InPostPayOrderInterface;
    public function getCourierNote(): ?string;
    public function setCourierNote(?string $courierNote): InPostPayOrderInterface;
    public function getInPostPayAccountEmail(): ?string;
    public function setInPostPayAccountEmail(?string $inPostPayAccountEmail): InPostPayOrderInterface;
    public function getDeliveryEmail(): ?string;
    public function setDeliveryEmail(?string $deliveryEmail): InPostPayOrderInterface;
    public function getDigitalDeliveryEmail(): ?string;
    public function setDigitalDeliveryEmail(?string $digitalDeliveryEmail): InPostPayOrderInterface;

    public function getGaClientId(): ?string;
    public function setGaClientId(?string $gaClientId): InPostPayOrderInterface;

    public function getFbclid(): ?string;
    public function setFbclid(?string $fbclid): InPostPayOrderInterface;

    public function getGclid(): ?string;
    public function setGclid(?string $gclid): InPostPayOrderInterface;

    public function getSerializedAnalyticsData(): ?string;
    public function setSerializedAnalyticsData(?string $serializedAnalyticsData): InPostPayOrderInterface;

    public function getAnalyticsSentAt(): ?string;
    public function setAnalyticsSentAt(?string $analyticsSentAt): InPostPayOrderInterface;

    /**
     * @return AcceptedConsentInterface[]
     */
    public function getAcceptedConsents(): array;

    /**
     * @param AcceptedConsentInterface[] $acceptedConsents
     * @return InPostPayOrderInterface
     */
    public function setAcceptedConsents(array $acceptedConsents): InPostPayOrderInterface;

    /**
     * @return bool
     */
    public function isOrderWithInvoice(): bool;

    /**
     * @param bool $orderWithInvoice
     * @return InPostPayOrderInterface
     */
    public function setOrderWithInvoice(bool $orderWithInvoice): InPostPayOrderInterface;

    /**
     * @return string|null
     */
    public function getInPostPayInvoiceEmail(): ?string;

    /**
     * @param string|null $inPostPayInvoiceEmail
     * @return InPostPayOrderInterface
     */
    public function setInPostPayInvoiceEmail(?string $inPostPayInvoiceEmail): InPostPayOrderInterface;

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getUpdatedAt(): string;

    /**
     * @return bool
     */
    public function isTimedOut(): bool;

    /**
     * @param bool $isTimedOut
     * @return InPostPayOrderInterface
     */
    public function setIsTimedOut(bool $isTimedOut): InPostPayOrderInterface;

    /**
     * @return bool
     */
    public function isTimeOutHandled(): bool;

    /**
     * @param bool $isTimeOutHandled
     * @return InPostPayOrderInterface
     */
    public function setIsTimeOutHandled(bool $isTimeOutHandled): InPostPayOrderInterface;
}
