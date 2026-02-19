<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PhoneNumberInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class InPostPayOrder extends AbstractModel implements InPostPayOrderInterface
{
    private const SEPARATOR = ',';

    protected $_eventPrefix = InPostPayOrderInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayOrderInterface::ENTITY_NAME;

    public function __construct(
        Context $context,
        Registry $registry,
        private readonly PhoneNumberInterfaceFactory $phoneNumberInterfaceFactory,
        private readonly AcceptedConsentInterfaceFactory $acceptedConsentFactory,
        private readonly SerializerInterface $serializer,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct(): void
    {
        $this->_init(ResourceModel\InPostPayOrder::class);
    }

    public function getInPostPayOrderId(): ?int
    {
        $id = ($this->hasData(self::INPOST_PAY_ORDER_ID)) ? $this->getData(self::INPOST_PAY_ORDER_ID) : null;

        return ($id && is_scalar($id)) ? (int)$id : null;
    }

    public function setInPostPayOrderId(int $inPostPayOrderId): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_ORDER_ID, $inPostPayOrderId);
    }

    public function getOrderId(): int
    {
        $orderId = $this->getData(self::ORDER_ID);

        if ($orderId && is_scalar($orderId)) {
            return (int)$orderId;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order ID value.'));
    }

    public function setOrderId(int $orderId): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getBasketId(): ?string
    {
        $basketId = $this->getData(self::BASKET_ID);

        return (is_scalar($basketId)) ? (string)$basketId : null;
    }

    public function setBasketId(?string $basketId): InPostPayOrderInterface
    {
        return $this->setData(self::BASKET_ID, $basketId);
    }

    public function getBasketBindingApiKey(): ?string
    {
        if ($this->hasData(self::BASKET_BINDING_API_KEY)) {
            $basketBindingApiKey = $this->getData(self::BASKET_BINDING_API_KEY);

            $basketBindingApiKey = is_scalar($basketBindingApiKey) ? (string)$basketBindingApiKey : null;
        };

        return $basketBindingApiKey ?? null;
    }

    public function setBasketBindingApiKey(?string $basketBindingApiKey): InPostPayOrderInterface
    {
        return $this->setData(self::BASKET_BINDING_API_KEY, $basketBindingApiKey);
    }

    public function getPaymentType(): ?string
    {
        $paymentType = $this->getData(self::PAYMENT_TYPE);

        return (is_scalar($paymentType)) ? (string)$paymentType : null;
    }

    public function setPaymentType(?string $paymentType): InPostPayOrderInterface
    {
        return $this->setData(self::PAYMENT_TYPE, $paymentType);
    }

    public function getLockerId(): ?string
    {
        $lockerId = ($this->hasData(self::LOCKER_ID)) ? $this->getData(self::LOCKER_ID) : null;

        return ($lockerId && is_scalar($lockerId)) ? (string)$lockerId : null;
    }

    public function setLockerId(string $lockerId): InPostPayOrderInterface
    {
        return $this->setData(self::LOCKER_ID, $lockerId);
    }

    public function getDeliveryOptions(): array
    {
        $deliveryOptions = $this->getData(self::DELIVERY_OPTIONS);
        if (!empty($deliveryOptions) && is_scalar($deliveryOptions)) {
            return explode(self::SEPARATOR, (string)$deliveryOptions);
        }

        return [];
    }

    public function setDeliveryOptions(array $deliveryOptions): InPostPayOrderInterface
    {
        return $this->setData(self::DELIVERY_OPTIONS, implode(self::SEPARATOR, $deliveryOptions));
    }

    /**
     * @return AcceptedConsentInterface[]
     */
    public function getAcceptedConsents(): array
    {
        $acceptedConsents = [];
        $acceptedContentsValue = $this->getData(self::ACCEPTED_CONSENTS);
        if (!empty($acceptedContentsValue) && is_scalar($acceptedContentsValue)) {
            $acceptedConsentsData = $this->serializer->unserialize((string)$acceptedContentsValue);
            if (is_array($acceptedConsentsData)) {
                foreach ($acceptedConsentsData as $acceptedConsentData) {
                    $consentId = (string)($acceptedConsentData[AcceptedConsentInterface::CONSENT_ID] ?? '');
                    $consentVersion = (string)($acceptedConsentData[AcceptedConsentInterface::CONSENT_VERSION] ?? '');
                    $isAccepted = (bool)($acceptedConsentData[AcceptedConsentInterface::IS_ACCEPTED] ?? false);

                    /** @var AcceptedConsentInterface $acceptedContent */
                    $acceptedContent = $this->acceptedConsentFactory->create();
                    $acceptedContent->setConsentId($consentId);
                    $acceptedContent->setConsentVersion($consentVersion);
                    $acceptedContent->setIsAccepted($isAccepted);
                    $acceptedConsents[] = $acceptedContent;
                }
            }
        }

        return $acceptedConsents;
    }

    /**
     * @param AcceptedConsentInterface[] $acceptedConsents
     * @return InPostPayOrderInterface
     */
    public function setAcceptedConsents(array $acceptedConsents): InPostPayOrderInterface
    {
        $acceptedConsentsData = [];
        foreach ($acceptedConsents as $acceptedConsent) {
            if ($acceptedConsent instanceof AcceptedConsentInterface) {
                $acceptedConsentsData[] = [
                    AcceptedConsentInterface::CONSENT_ID => $acceptedConsent->getConsentId(),
                    AcceptedConsentInterface::CONSENT_VERSION => $acceptedConsent->getConsentVersion(),
                    AcceptedConsentInterface::IS_ACCEPTED => $acceptedConsent->getIsAccepted()
                ];
            }
        }
        return $this->setData(self::ACCEPTED_CONSENTS, $this->serializer->serialize($acceptedConsentsData));
    }

    /**
     * @return bool
     */
    public function isOrderWithInvoice(): bool
    {
        $orderWithInvoice = $this->getData(self::ORDER_WITH_INVOICE);

        return is_scalar($orderWithInvoice) && (bool)$orderWithInvoice;
    }

    /**
     * @param bool $orderWithInvoice
     * @return InPostPayOrderInterface
     */
    public function setOrderWithInvoice(bool $orderWithInvoice): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_WITH_INVOICE, $orderWithInvoice);
    }

    /**
     * @return string|null
     */
    public function getInPostPayInvoiceEmail(): ?string
    {
        $inPostPayInvoiceEmail = $this->getData(self::INPOST_PAY_INVOICE_EMAIL);

        return is_scalar($inPostPayInvoiceEmail) ? (string)$inPostPayInvoiceEmail : null;
    }

    /**
     * @param string|null $inPostPayInvoiceEmail
     * @return InPostPayOrderInterface
     */
    public function setInPostPayInvoiceEmail(?string $inPostPayInvoiceEmail): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_INVOICE_EMAIL, $inPostPayInvoiceEmail);
    }

    public function getOrderStatus(): ?string
    {
        $orderStatus = ($this->hasData(self::ORDER_STATUS)) ? $this->getData(self::ORDER_STATUS) : null;

        return ($orderStatus && is_scalar($orderStatus)) ? (string)$orderStatus : null;
    }

    public function setOrderStatus(string $orderStatus): InPostPayOrderInterface
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    public function getPhone(): ?string
    {
        $phone = ($this->hasData(self::PHONE)) ? $this->getData(self::PHONE) : null;

        return ($phone && is_scalar($phone)) ? (string)$phone : null;
    }

    public function setPhone(string $phone): InPostPayOrderInterface
    {
        return $this->setData(self::PHONE, $phone);
    }

    public function getCourierNote(): ?string
    {
        $courierNote = ($this->hasData(self::COURIER_NOTE)) ? $this->getData(self::COURIER_NOTE) : null;

        return ($courierNote && is_scalar($courierNote)) ? (string)$courierNote : null;
    }

    public function setCourierNote(?string $courierNote): InPostPayOrderInterface
    {
        return $this->setData(self::COURIER_NOTE, $courierNote);
    }

    public function getGaClientId(): ?string
    {
        $gaClientId = $this->getData(self::GA_CLIENT_ID);

        return (is_scalar($gaClientId) && !empty($gaClientId)) ? (string)$gaClientId : null;
    }

    public function setGaClientId(?string $gaClientId): InPostPayOrderInterface
    {
        return $this->setData(self::GA_CLIENT_ID, $gaClientId);
    }

    public function getFbclid(): ?string
    {
        $fbclid = $this->getData(self::FBCLID);

        return (is_scalar($fbclid) && !empty($fbclid)) ? (string)$fbclid : null;
    }

    public function setFbclid(?string $fbclid): InPostPayOrderInterface
    {
        return $this->setData(self::FBCLID, $fbclid);
    }

    public function getGclid(): ?string
    {
        $gclid = $this->getData(self::GCLID);

        return (is_scalar($gclid) && !empty($gclid)) ? (string)$gclid : null;
    }

    public function setGclid(?string $gclid): InPostPayOrderInterface
    {
        return $this->setData(self::GCLID, $gclid);
    }

    public function isTimedOut(): bool
    {
        $isTimedOut = $this->getData(self::IS_TIMED_OUT);

        return is_scalar($isTimedOut) && (bool)$isTimedOut;
    }

    public function setIsTimedOut(bool $isTimedOut): InPostPayOrderInterface
    {
        return $this->setData(self::IS_TIMED_OUT, $isTimedOut);
    }

    public function isTimeOutHandled(): bool
    {
        $isHandled = $this->getData(self::IS_TIME_OUT_HANDLED);

        return is_scalar($isHandled) && (bool)$isHandled;
    }

    public function setIsTimeOutHandled(bool $isTimeOutHandled): InPostPayOrderInterface
    {
        return $this->setData(self::IS_TIME_OUT_HANDLED, $isTimeOutHandled);
    }

    public function getSerializedAnalyticsData(): ?string
    {
        $serializedData = $this->getData(self::SERIALIZED_ANALYTICS_DATA);

        return (is_scalar($serializedData) && !empty($serializedData)) ? (string)$serializedData : null;
    }

    public function setSerializedAnalyticsData(?string $serializedAnalyticsData): InPostPayOrderInterface
    {
        return $this->setData(self::SERIALIZED_ANALYTICS_DATA, $serializedAnalyticsData);
    }

    public function getAnalyticsSentAt(): ?string
    {
        $sentAt = $this->getData(self::ANALYTICS_SENT_AT);

        return (is_scalar($sentAt) && !empty($sentAt)) ? (string)$sentAt : null;
    }

    public function setAnalyticsSentAt(?string $analyticsSentAt): InPostPayOrderInterface
    {
        return $this->setData(self::ANALYTICS_SENT_AT, $analyticsSentAt);
    }

    public function getInPostPayAccountEmail(): ?string
    {
        $hasInPostPayAccountEmail = $this->hasData(self::INPOST_PAY_ACCOUNT_EMAIL);
        $inPostPayAccountEmail = $hasInPostPayAccountEmail ? $this->getData(self::INPOST_PAY_ACCOUNT_EMAIL) : null;

        return ($inPostPayAccountEmail && is_scalar($inPostPayAccountEmail)) ? (string)$inPostPayAccountEmail : null;
    }

    public function setInPostPayAccountEmail(?string $inPostPayAccountEmail): InPostPayOrderInterface
    {
        return $this->setData(self::INPOST_PAY_ACCOUNT_EMAIL, $inPostPayAccountEmail);
    }

    public function getDeliveryEmail(): ?string
    {
        $deliveryEmail = $this->hasData(self::DELIVERY_EMAIL);
        $deliveryEmail = $deliveryEmail ? $this->getData(self::DELIVERY_EMAIL) : null;

        return ($deliveryEmail && is_scalar($deliveryEmail)) ? (string)$deliveryEmail : null;
    }

    public function setDeliveryEmail(?string $deliveryEmail): InPostPayOrderInterface
    {
        return $this->setData(self::DELIVERY_EMAIL, $deliveryEmail);
    }

    public function getDigitalDeliveryEmail(): ?string
    {
        $hasDigitalDeliveryEmail = $this->hasData(self::DIGITAL_DELIVERY_EMAIL);
        $digitalDeliveryEmail = $hasDigitalDeliveryEmail ? $this->getData(self::DIGITAL_DELIVERY_EMAIL) : null;

        return ($digitalDeliveryEmail && is_scalar($digitalDeliveryEmail)) ? (string)$digitalDeliveryEmail : null;
    }

    public function setDigitalDeliveryEmail(?string $digitalDeliveryEmail): InPostPayOrderInterface
    {
        return $this->setData(self::DIGITAL_DELIVERY_EMAIL, $digitalDeliveryEmail);
    }

    public function getCountryPrefix(): ?string
    {
        $countryPrefix = ($this->hasData(self::COUNTRY_PREFIX)) ? $this->getData(self::COUNTRY_PREFIX) : null;

        return ($countryPrefix && is_scalar($countryPrefix)) ? (string)$countryPrefix : null;
    }

    public function setCountryPrefix(string $countryPrefix): InPostPayOrderInterface
    {
        return $this->setData(self::COUNTRY_PREFIX, $countryPrefix);
    }

    public function getPhoneNumber(): PhoneNumberInterface
    {
        $phoneNumber = $this->getData(self::PHONE_NUMBER);
        if (!$phoneNumber instanceof PhoneNumberInterface) {
            $phoneNumber = $this->phoneNumberInterfaceFactory->create();
        }

        $phoneNumber->setPhone((string)$this->getPhone());
        $phoneNumber->setCountryPrefix((string)$this->getCountryPrefix());

        return $phoneNumber;
    }

    public function getCreatedAt(): string
    {
        $createdAt = $this->getData(self::CREATED_AT);

        if ($createdAt && is_scalar($createdAt)) {
            return (string)$createdAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order created at value.'));
    }

    public function getUpdatedAt(): string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);

        if ($updatedAt && is_scalar($updatedAt)) {
            return (string)$updatedAt;
        }

        throw new LocalizedException(__('Invalid InPost Pay Order updated at value.'));
    }
}
