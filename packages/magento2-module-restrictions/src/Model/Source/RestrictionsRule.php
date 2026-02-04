<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\Source;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Data\OptionSourceInterface;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRule\CollectionFactory;
use InPost\Restrictions\Model\ResourceModel\RestrictionsRule\Collection;

class RestrictionsRule implements OptionSourceInterface
{
    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        $options = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        /** @var RestrictionsRuleInterface $rule */
        foreach ($collection->getItems() as $rule) {
            $options[] = [
                'value' => (int)$rule->getRuleId(),
                'label' => (string)$rule->getName()
            ];
        }

        return $options;
    }
}
