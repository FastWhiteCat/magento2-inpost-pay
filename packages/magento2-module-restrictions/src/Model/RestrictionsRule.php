<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRuleProduct\CollectionFactory as RuleProductCollectionFactory;
use InPost\Restrictions\Provider\RuleMatchingProductsProvider;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as ActionCollectionFactory;
use Magento\CatalogRule\Model\Rule\Action\Collection as ActionCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestrictionsRule extends AbstractModel implements RestrictionsRuleInterface
{
    protected $_eventPrefix = RestrictionsRuleInterface::ENTITY_NAME;
    protected $_eventObject = RestrictionsRuleInterface::ENTITY_NAME;

    /**
     * @var int[]
     */
    protected array $conditionProductIds = [];

    /**
     * @var int[]
     */
    protected array $ruleProductIds = [];

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param ActionCollectionFactory $actionCollectionFactory
     * @param RuleMatchingProductsProvider $ruleMatchingProductsProvider
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        private readonly CombineFactory $combineFactory,
        private readonly ActionCollectionFactory $actionCollectionFactory,
        private readonly RuleMatchingProductsProvider $ruleMatchingProductsProvider,
        private readonly StoreManagerInterface $storeManager,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct(): void
    {
        $this->_init(ResourceModel\RestrictionsRule::class);
        $this->setIdFieldName(RestrictionsRuleInterface::RULE_ID);
    }

    public function getRuleId(): ?int
    {
        return (is_scalar($this->getData(self::RULE_ID))) ? (int) $this->getData(self::RULE_ID) : null;
    }

    public function setRuleId(int $ruleId): RestrictionsRuleInterface
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    public function getName(): string
    {
        return is_scalar($this->getData(self::NAME)) ? (string)$this->getData(self::NAME) :  '';
    }

    public function setName(string $name): RestrictionsRuleInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getIsEnabled(): bool
    {
        return is_scalar($this->getData(self::IS_ENABLED)) && (bool)$this->getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $isEnabled): RestrictionsRuleInterface
    {
        return $this->setData(self::IS_ENABLED, (int)$isEnabled);
    }

    public function getWebsiteId(): int
    {
        return is_scalar($this->getData(self::WEBSITE_ID)) ? (int)$this->getData(self::WEBSITE_ID) : 0;
    }

    public function setWebsiteId(int $websiteId): RestrictionsRuleInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    public function getAppliesTo(): int
    {
        return is_scalar($this->getData(self::APPLIES_TO)) ? (int)$this->getData(self::APPLIES_TO) : 0;
    }

    public function setAppliesTo(int $appliesTo): RestrictionsRuleInterface
    {
        return $this->setData(self::APPLIES_TO, $appliesTo);
    }

    public function getCreatedAt(): string
    {
        return is_scalar($this->getData(self::CREATED_AT)) ? (string)$this->getData(self::CREATED_AT) : '';
    }

    public function getUpdatedAt(): string
    {
        return is_scalar($this->getData(self::UPDATED_AT)) ? (string)$this->getData(self::UPDATED_AT) : '';
    }

    public function getConditionsSerialized(): ?string
    {
        $conditionsSerialized = $this->getData(self::CONDITIONS_SERIALIZED);

        return is_scalar($conditionsSerialized)? (string)$conditionsSerialized : null;
    }

    public function setConditionsSerialized(string $conditionsSerialized): RestrictionsRuleInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    public function getConditionsInstance(): Combine
    {
        return $this->combineFactory->create();
    }

    public function getActionsInstance(): ActionCollection
    {
        return $this->actionCollectionFactory->create();
    }

    public function getConditionsFieldSetId(string $formName = ''): string
    {
        $id = is_scalar($this->getId()) ? (int)$this->getId() : 0;

        return sprintf('%srule_conditions_fieldset_%s', $formName, $id);
    }

    public function getProductIdsByConditions(): array
    {
        if (empty($this->conditionProductIds)) {
            $conditionProductIds = [];
            foreach ($this->storeManager->getStores() as $store) {
                if ((int)$store->getWebsiteId() !== $this->getWebsiteId()) {
                    continue;
                }

                $conditionProductIds = array_merge( // phpcs:ignore
                    $this->ruleMatchingProductsProvider->getUsingConditions($this, (int) $store->getId()),
                    $conditionProductIds
                );
            }

            $this->conditionProductIds = array_unique($conditionProductIds);
        }

        return $this->conditionProductIds;
    }

    public function getRuleProductIds(): array
    {
        if (empty($this->ruleProductIds)) {
            $this->ruleProductIds = $this->ruleMatchingProductsProvider->getUsingIndex($this);
        }

        return $this->ruleProductIds;
    }
}
