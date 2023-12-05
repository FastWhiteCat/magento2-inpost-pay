<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator\Order;

use InPost\InPostPay\Api\ApiConnector\IziApi\Consent\ConsentFieldInterface as ConsentField;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\Validator\OrderValidatorInterface;
use InPost\InPostPay\Model\Config\Source\TermsAndConditionsRequirements;
use InPost\InPostPay\Model\Dto\Order as DtoOrder;
use InPost\InPostPay\Model\Dto\Order\Consent;
use InPost\InPostPay\Provider\ConsentsProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class ConsentsValidator implements OrderValidatorInterface
{
    public function __construct(
        private readonly ConsentsProvider $consentsProvider
    ) {
    }

    public function validate(Quote $quote, InPostPayQuoteInterface $inPostPayQuote, DtoOrder $orderDto): void
    {
        $orderConsents = $orderDto->getConsents();
        foreach ($this->consentsProvider->getConsents() as $configConsent) {
            $consentId = (string)($configConsent[ConsentField::CONSENT_ID] ?? '');
            $consentVersion = (string)($configConsent[ConsentField::CONSENT_VERSION] ?? '');
            $requirementType = $configConsent[ConsentField::REQUIREMENT_TYPE] ?? '';
            if ($requirementType === TermsAndConditionsRequirements::ALWAYS
                || $requirementType === TermsAndConditionsRequirements::ONLY_IN_NEW_VERSION
            ) {
                try {
                    $this->checkIfAcceptedAndVersion($orderConsents, $consentId, $consentVersion);
                } catch (LocalizedException $e) {
                    throw new LocalizedException(
                        __('Consents validation failed. Reason: %1', $e->getMessage())
                    );
                }

            }
        }
    }

    /**
     * @param Consent[] $orderConsents
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

                if (!$orderConsent->isAccepted()) {
                    throw new LocalizedException(__('Consent %1 has not been accepted.', $consentId));
                }
            }
        }
    }
}
