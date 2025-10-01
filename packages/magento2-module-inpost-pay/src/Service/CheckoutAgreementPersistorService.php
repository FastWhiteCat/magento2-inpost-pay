<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface as AgreementInterface;
use Magento\Framework\Exception\LocalizedException;
use InPost\InPostPay\Api\InPostPayCheckoutAgreementRepositoryInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterfaceFactory;

class CheckoutAgreementPersistorService
{
    /**
     * @param InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository
     * @param InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementFactory
     */
    public function __construct(
        private readonly InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository,
        private readonly InPostPayCheckoutAgreementInterfaceFactory $inPostPayCheckoutAgreementFactory
    ) {
    }

    /**
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $data): void
    {
        if (isset($data[AgreementInterface::AGREEMENT_ID])) {
            $agreement = $this->inPostPayCheckoutAgreementRepository->get(
                (int)$data[AgreementInterface::AGREEMENT_ID]
            );
        } else {
            $agreement = $this->inPostPayCheckoutAgreementFactory->create();
        }

        $title = (string) ($data[AgreementInterface::TITLE] ?? '');
        $isEnabled = (bool) ($data[AgreementInterface::IS_ENABLED] ?? false);
        $visibility = (int) ($data[AgreementInterface::VISIBILITY] ?? AgreementInterface::VISIBILITY_MAIN);
        $agreementUrl = (string) ($data[AgreementInterface::AGREEMENT_URL] ?? '');
        $urlLabel = (string) ($data[AgreementInterface::URL_LABEL] ?? '');
        $requirement = (string) ($data[AgreementInterface::REQUIREMENT] ?? AgreementInterface::REQUIREMENT_OPTIONAL);
        $checkboxText = (string) ($data[AgreementInterface::CHECKBOX_TEXT] ?? '');

        $childrenIds = ($data[AgreementInterface::CHILDREN_IDS] ?? []);
        $storeIds = (array) ($data[AgreementInterface::STORE_IDS] ?? []);
        $version = uniqid();

        $agreement->setTitle($title);
        $agreement->setIsEnabled($isEnabled);
        $agreement->setVisibility($visibility);
        $agreement->setAgreementUrl($agreementUrl);
        $agreement->setUrlLabel($urlLabel);
        $agreement->setRequirement($requirement);
        $agreement->setCheckboxText($checkboxText);
        $agreement->setStoreIds($storeIds);

        if (is_array($childrenIds) && !empty($childrenIds)) {
            $childAgreementIds = [];

            foreach ($childrenIds as $childrenId) {
                $childrenId = is_scalar($childrenId) ? (int)$childrenId : 0;
                $childAgreement = $this->inPostPayCheckoutAgreementRepository->get($childrenId);
                $childAgreementIds[] = (int)$childAgreement->getAgreementId();
            }

            $agreement->setChildrenIds(implode(',', $childAgreementIds));
        } else {
            $agreement->setChildrenIds(null);
        }

        $agreement->setVersion($version);

        $this->inPostPayCheckoutAgreementRepository->save($agreement);
    }
}
