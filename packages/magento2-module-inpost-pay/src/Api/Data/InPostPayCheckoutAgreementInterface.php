<?php

declare(strict_types=1);

namespace InPost\InPostPay\Api\Data;

interface InPostPayCheckoutAgreementInterface
{
    public const TABLE_NAME = 'inpost_pay_checkout_agreement';
    public const ENTITY_NAME = 'inpost_pay_checkout_agreement';
    public const AGREEMENT_ID = 'agreement_id';
    public const TITLE = 'title';
    public const STORE_IDS = 'store_ids';
    public const ASSIGNED_STORE_RECORDS = 'assigned_store_records';
    public const IS_ENABLED = 'is_enabled';
    public const VISIBILITY = 'visibility';
    public const VISIBILITY_MAIN = 1;
    public const VISIBILITY_CHILD = 2;
    public const REQUIREMENT = 'requirement';
    public const REQUIREMENT_OPTIONAL = 'OPTIONAL';
    public const REQUIREMENT_REQUIRED_ONCE = 'REQUIRED_ONCE';
    public const REQUIREMENT_REQUIRED_ALWAYS = 'REQUIRED_ALWAYS';
    public const CHILDREN_IDS = 'children_ids';
    public const CHILDREN_AGREEMENTS = 'children_agreements';
    public const CHECKBOX_TEXT = 'checkbox_text';
    public const URL_LABEL = 'url_label';
    public const URL_LABEL_PATTERN = '{URL}';
    public const AGREEMENT_URL = 'agreement_url';
    public const VERSION = 'version';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getAgreementId(): ?int;

    /**
     * @param int $agreementId
     * @return void
     */
    public function setAgreementId(int $agreementId): void;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $isEnabled
     * @return void
     */
    public function setIsEnabled(bool $isEnabled): void;

    /**
     * @return array
     */
    public function getStoreIds(): array;

    /**
     * @return InPostPayCheckoutAgreementStoreInterface[]
     */
    public function getAssignedStoreRecords(): array;

    /**
     * @param int[] $storeIds
     * @return void
     */
    public function setStoreIds(array $storeIds): void;

    /**
     * @return int
     */
    public function getVisibility(): int;

    /**
     * @param int $visibility
     * @return void
     */
    public function setVisibility(int $visibility): void;

    /**
     * @return string
     */
    public function getRequirement(): string;

    /**
     * @param string $requirement
     * @return void
     */
    public function setRequirement(string $requirement): void;

    /**
     * @return string|null
     */
    public function getChildrenIds(): ?string;

    /**
     * @param string|null $childrenIds
     * @return void
     */
    public function setChildrenIds(?string $childrenIds): void;

    /**
     * @return InPostPayCheckoutAgreementInterface[]|null
     */
    public function getChildrenAgreements(): ?array;

    /**
     * @return string
     */
    public function getCheckboxText(): string;

    /**
     * @param string $checkboxText
     * @return void
     */
    public function setCheckboxText(string $checkboxText): void;

    /**
     * @return string
     */
    public function getAgreementUrl(): string;

    /**
     * @param string $agreementUrl
     * @return void
     */
    public function setAgreementUrl(string $agreementUrl): void;

    /**
     * @return string
     */
    public function getUrlLabel(): string;

    /**
     * @param string $urlLabel
     * @return void
     */
    public function setUrlLabel(string $urlLabel): void;

    /**
     * @return string
     */
    public function getVersion(): string;

    /**
     * @param string $version
     * @return void
     */
    public function setVersion(string $version): void;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;
}
