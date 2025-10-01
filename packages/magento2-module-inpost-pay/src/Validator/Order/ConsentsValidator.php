<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\Data\Merchant\Order\AcceptedConsentInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Model\Config\Source\TermsAndConditionsRequirements;
use InPost\InPostPay\Provider\Config\TermsAndConditionsConfigProvider;
use InPost\InPostPay\Provider\ConsentsProvider;
use InPost\InPostPay\Provider\LegacyConsentsProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class ConsentsValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly TermsAndConditionsConfigProvider $termsAndConditionsConfigProvider,
        private readonly LegacyConsentsProvider $legacyConsentsProvider,
        private readonly ConsentsProvider $consentsProvider
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, OrderInterface $inPostOrder): void
    {
        $acceptedConsents = $inPostOrder->getConsents();

        if ($this->termsAndConditionsConfigProvider->isLegacyMappingEnabled()) {
            $consentProvider = $this->legacyConsentsProvider;
        } else {
            $consentProvider = $this->consentsProvider;
        }

        foreach ($consentProvider->getConsents($quote->getStoreId()) as $configConsent) {
            $consentId = (string)($configConsent[AcceptedConsentInterface::CONSENT_ID] ?? '');
            $consentVersion = (string)($configConsent[AcceptedConsentInterface::CONSENT_VERSION] ?? '');
            $requirementType = $configConsent[AcceptedConsentInterface::REQUIREMENT_TYPE] ?? '';

            if ($requirementType === TermsAndConditionsRequirements::ALWAYS
                || $requirementType === TermsAndConditionsRequirements::ONLY_IN_NEW_VERSION
            ) {
                try {
                    $this->checkIfAcceptedAndVersion($acceptedConsents, $consentId, $consentVersion);
                } catch (LocalizedException $e) {
                    throw new LocalizedException(
                        __('Consents validation failed. Reason: %1', $e->getMessage())
                    );
                }
            }
        }
    }

    /**
     * @param AcceptedConsentInterface[] $orderConsents
     * @param string $consentId
     * @param string $version
     * @return void
     * @throws LocalizedException
     */
    private function checkIfAcceptedAndVersion(array $orderConsents, string $consentId, string $version): void
    {
        foreach ($orderConsents as $orderConsent) {
            if ((string)$orderConsent->getConsentId() === $consentId) {
                if ($orderConsent->getConsentVersion() !== $version) {
                    throw new LocalizedException(
                        __(
                            'Consent %1 version is not up to date. Expected: %2 Received: %3',
                            $consentId,
                            $version,
                            $orderConsent->getConsentVersion()
                        )
                    );
                }

                if (!$orderConsent->getIsAccepted()) {
                    throw new LocalizedException(__('Consent %1 has not been accepted.', $consentId));
                }
            }
        }
    }
}
