<?php

declare(strict_types=1);

namespace InPost\Restrictions\Model\Source\Store;

use Magento\Framework\Data\OptionSourceInterface;

use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use Magento\Store\Model\ResourceModel\Website\Collection;

class Website implements OptionSourceInterface
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

        /** @var WebsiteInterface $website */
        foreach ($collection->getItems() as $website) {
            $options[] = [
                'value' => (int)$website->getId(),
                'label' => (string)$website->getName()
            ];
        }

        return $options;
    }
}
