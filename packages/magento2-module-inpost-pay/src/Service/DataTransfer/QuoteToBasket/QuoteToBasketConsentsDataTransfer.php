<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Consent\AdditionalConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Consent\AdditionalConsentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\Config\TermsAndConditionsConfigProvider;
use InPost\InPostPay\Provider\ConsentsProvider;
use InPost\InPostPay\Provider\LegacyConsentsProvider;
use Magento\Quote\Model\Quote;

class QuoteToBasketConsentsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ConsentInterfaceFactory $consentFactory,
        private readonly AdditionalConsentInterfaceFactory $additionalConsentFactory,
        private readonly ConsentsProvider $consentsProvider,
        private readonly LegacyConsentsProvider $legacyConsentsProvider,
        private readonly TermsAndConditionsConfigProvider $termsAndConditionsConfigProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $consents = [];
        if ($this->termsAndConditionsConfigProvider->isLegacyMappingEnabled()) {
            $consentProvider = $this->legacyConsentsProvider;
        } else {
            $consentProvider = $this->consentsProvider;
        }

        foreach ($consentProvider->getConsents($quote->getStoreId()) as $consentData) {
            $additionalConsents = (array)($consentData[ConsentInterface::ADDITIONAL_CONSENT_LINKS] ?? []);
            $additionalConsentLinks = [];

            foreach ($additionalConsents as $additionalConsent) {
                /** @var AdditionalConsentInterface $additionalConsentLink */
                $additionalConsentLink = $this->additionalConsentFactory->create();
                $additionalConsentLink->setId((string)$additionalConsent[ConsentInterface::CONSENT_ID]);
                $additionalConsentLink->setConsentLink($additionalConsent[ConsentInterface::CONSENT_LINK]);
                $additionalConsentLink->setLabelLink($additionalConsent[ConsentInterface::LABEL_LINK]);
                $additionalConsentLinks[] = $additionalConsentLink;
            }

            /** @var ConsentInterface $consent */
            $consent = $this->consentFactory->create();
            $consent->setConsentId((string)$consentData[ConsentInterface::CONSENT_ID]);
            $consent->setConsentLink((string)$consentData[ConsentInterface::CONSENT_LINK]);
            $consent->setConsentDescription((string)$consentData[ConsentInterface::CONSENT_DESCRIPTION]);
            $consent->setAdditionalConsentLinks($additionalConsentLinks);
            $consent->setLabelLink((string)$consentData[ConsentInterface::LABEL_LINK]);
            $consent->setConsentVersion((string)$consentData[ConsentInterface::CONSENT_VERSION]);
            $consent->setRequirementType($consentData[ConsentInterface::REQUIREMENT_TYPE]);
            $consents[] = $consent;
        };

        $basket->setConsents($consents);
    }
}
