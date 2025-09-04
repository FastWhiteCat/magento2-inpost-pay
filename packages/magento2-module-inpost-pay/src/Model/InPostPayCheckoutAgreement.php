<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementStoreInterface;
use InPost\InPostPay\Enum\InPostConsentRequirementType;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore\Collection as AgreementStoreCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreementStore\CollectionFactory
    as AgreementStoreCollectionFactory;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\Collection as AgreementCollection;
use InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement\CollectionFactory as AgreementCollectionFactory;

class InPostPayCheckoutAgreement extends AbstractModel implements InPostPayCheckoutAgreementInterface
{
    protected $_eventPrefix = InPostPayCheckoutAgreementInterface::ENTITY_NAME;
    protected $_eventObject = InPostPayCheckoutAgreementInterface::ENTITY_NAME;

    public function __construct(
        private readonly AgreementStoreCollectionFactory $agreementStoreCollectionFactory,
        private readonly AgreementCollectionFactory $agreementCollectionFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct(): void
    {
        $this->_init(\InPost\InPostPay\Model\ResourceModel\InPostPayCheckoutAgreement::class);
    }

    /**
     * @return int|null
     */
    public function getAgreementId(): ?int
    {
        return is_scalar($this->getData(self::AGREEMENT_ID)) ? (int) $this->getData(self::AGREEMENT_ID) : null;
    }

    /**
     * @param int $agreementId
     * @return void
     */
    public function setAgreementId(int $agreementId): void
    {
        $this->setData(self::AGREEMENT_ID, $agreementId);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return is_scalar($this->getData(self::TITLE)) ? (string)$this->getData(self::TITLE) : '';
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->setData(self::TITLE, $title);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return is_bool($this->getData(self::IS_ENABLED)) && (bool)$this->getData(self::IS_ENABLED);
    }

    /**
     * @param bool $isEnabled
     * @return void
     */
    public function setIsEnabled(bool $isEnabled): void
    {
        $this->setData(self::IS_ENABLED, $isEnabled);
    }

    public function getStoreIds(): array
    {
        /** @var int[]|null $storeIds */
        $storeIds = $this->getData(self::STORE_IDS);
        $agreementId = $this->getAgreementId();

        if ($agreementId === null) {
            return [];
        }

        if (empty($storeIds)) {
            $storeIds = [];

            foreach ($this->getAssignedStoreRecords() as $assignedStoreRecord) {
                if ($assignedStoreRecord instanceof InPostPayCheckoutAgreementStoreInterface

                ) {
                    $storeIds[] = $assignedStoreRecord->getStoreId();
                }
            }

            $this->setStoreIds($storeIds);
        }

        return $storeIds;
    }

    /**
     * @return InPostPayCheckoutAgreementStoreInterface[]
     */
    public function getAssignedStoreRecords(): array
    {
        /** @var InPostPayCheckoutAgreementStoreInterface[]|null $assignedStoreRecords */
        $assignedStoreRecords = $this->getData(self::ASSIGNED_STORE_RECORDS);
        $agreementId = $this->getAgreementId();

        if ($agreementId === null) {
            return [];
        }

        if (empty($assignedStoreRecords)) {
            /** @var AgreementStoreCollection $agreementStoreCollection */
            $agreementStoreCollection = $this->agreementStoreCollectionFactory->create();
            $agreementStoreCollection->addFieldToFilter(
                InPostPayCheckoutAgreementStoreInterface::AGREEMENT_ID,
                ['eq' => $agreementId]
            );
            $assignedStoreRecords = [];

            foreach ($agreementStoreCollection->getItems() as $item) {
                if ($item instanceof InPostPayCheckoutAgreementStoreInterface) {
                    $assignedStoreRecords[] = $item;
                }
            }

            $this->setData(self::ASSIGNED_STORE_RECORDS, $assignedStoreRecords);
        }

        return $assignedStoreRecords;
    }

    public function setStoreIds(array $storeIds): void
    {
        $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * @return int
     */
    public function getVisibility(): int
    {
        $visibility = $this->getData(self::VISIBILITY);

        return is_scalar($visibility) ? (int)$visibility : self::VISIBILITY_MAIN;
    }

    /**
     * @param int $visibility
     * @return void
     */
    public function setVisibility(int $visibility): void
    {
        $this->setData(self::VISIBILITY, $visibility);
    }

    /**
     * @return string
     */
    public function getRequirement(): string
    {
        $requirement = $this->getData(self::REQUIREMENT);

        return is_string($requirement) ? (string)$requirement : InPostConsentRequirementType::OPTIONAL->value;
    }

    /**
     * @param string $requirement
     * @return void
     */
    public function setRequirement(string $requirement): void
    {
        $this->setData(self::REQUIREMENT, $requirement);
    }

    /**
     * @return string|null
     */
    public function getChildrenIds(): ?string
    {
        return is_scalar($this->getData(self::CHILDREN_IDS)) ? (string) $this->getData(self::CHILDREN_IDS) : null;
    }

    /**
     * @param string|null $childrenIds
     * @return void
     */
    public function setChildrenIds(?string $childrenIds): void
    {
        $this->setData(self::CHILDREN_IDS, $childrenIds);
    }

    /**
     * @return string
     */
    public function getCheckboxText(): string
    {
        return is_scalar($this->getData(self::CHECKBOX_TEXT)) ? (string)$this->getData(self::CHECKBOX_TEXT) : '';
    }

    /**
     * @param string $checkboxText
     * @return void
     */
    public function setCheckboxText(string $checkboxText): void
    {
        $this->setData(self::CHECKBOX_TEXT, $checkboxText);
    }

    public function getChildrenAgreements(): ?array
    {
        /** @var InPostPayCheckoutAgreementInterface[]|null $childAgreements */
        $childAgreements = $this->getData(self::CHILDREN_AGREEMENTS);

        if (!empty($childAgreements)) {
            return $childAgreements;
        }

        $childrenAgreementIds = is_scalar($this->getChildrenIds()) ? (string)$this->getChildrenIds() : null;

        if ($childrenAgreementIds === null) {
            return [];
        }

        $childrenAgreementIdsArray = array_map('intval', explode(',', $childrenAgreementIds));

        /** @var AgreementCollection $agreementCollection */
        $agreementCollection = $this->agreementCollectionFactory->create();
        $agreementCollection->addFieldToFilter(
            InPostPayCheckoutAgreementInterface::AGREEMENT_ID,
            ['in' => $childrenAgreementIdsArray]
        );
        $agreementCollection->addFieldToFilter(
            InPostPayCheckoutAgreementInterface::VISIBILITY,
            ['eq' => InPostPayCheckoutAgreementInterface::VISIBILITY_CHILD]
        );
        $agreementCollection->addFieldToFilter(InPostPayCheckoutAgreementInterface::IS_ENABLED, ['eq' => 1]);
        $childAgreements = [];

        foreach ($agreementCollection->getItems() as $item) {
            if ($item instanceof InPostPayCheckoutAgreementInterface) {
                $childAgreements[] = $item;
            }
        }

        $this->setData(self::CHILDREN_AGREEMENTS, $childAgreements);

        return $childAgreements;
    }

    public function getAgreementUrl(): string
    {
        return is_scalar($this->getData(self::AGREEMENT_URL)) ? (string)$this->getData(self::AGREEMENT_URL) : '';
    }

    public function setAgreementUrl(string $agreementUrl): void
    {
        $this->setData(self::AGREEMENT_URL, $agreementUrl);
    }

    public function getUrlLabel(): string
    {
        return is_scalar($this->getData(self::URL_LABEL)) ? (string)$this->getData(self::URL_LABEL) : '';
    }

    public function setUrlLabel(string $urlLabel): void
    {
        $this->setData(self::URL_LABEL, $urlLabel);
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return is_scalar($this->getData(self::VERSION)) ? (string)$this->getData(self::VERSION) : '';
    }

    /**
     * @param string $version
     * @return void
     */
    public function setVersion(string $version): void
    {
        $this->setData(self::VERSION, $version);
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return is_scalar($this->getData(self::CREATED_AT)) ? (string)$this->getData(self::CREATED_AT) : '';
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return is_scalar($this->getData(self::UPDATED_AT)) ? (string)$this->getData(self::UPDATED_AT) : '';
    }

    /**
     * Set updated at timestamp.
     *
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
