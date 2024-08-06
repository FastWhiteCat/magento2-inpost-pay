<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ConsentInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Provider\ConsentsProvider;
use Magento\Quote\Model\Quote;

class QuoteToBasketConsentsDataTransfer implements QuoteToBasketDataTransferInterface
{
    public function __construct(
        private readonly ConsentInterfaceFactory $consentFactory,
        private readonly ConsentsProvider $consentsProvider
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $consents = [];
        foreach ($this->consentsProvider->getConsents() as $consentData) {
            /** @var ConsentInterface $consent */
            $consent = $this->consentFactory->create();
            $consent->setConsentId($consentData[ConsentInterface::CONSENT_ID]);
            $consent->setConsentLink($consentData[ConsentInterface::CONSENT_LINK]);
            $consent->setConsentDescription($consentData[ConsentInterface::CONSENT_DESCRIPTION]);
            $consent->setConsentVersion($consentData[ConsentInterface::CONSENT_VERSION]);
            $consent->setRequirementType($consentData[ConsentInterface::REQUIREMENT_TYPE]);
            $consents[] = $consent;
        };

        $basket->setConsents($consents);
    }
}
