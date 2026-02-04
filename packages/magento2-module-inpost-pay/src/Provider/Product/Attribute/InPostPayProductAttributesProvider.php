<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Product\Attribute;

use InPost\InPostPay\Provider\Config\OmnibusConfigProvider;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as EavAttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavAttributeCollection;

class InPostPayProductAttributesProvider
{
    private ?array $inPostPayProductAttributes = null;

    public function __construct(
        private readonly EavAttributeCollectionFactory $eavAttributeCollectionFactory,
        private readonly OmnibusConfigProvider $omnibusConfigProvider,
        private readonly ProductFactory $productFactory
    ) {
    }

    /**
     * @param int|null $storeId
     * @return string[]
     */
    public function getProductAttributeCodes(?int $storeId = null): array
    {
        if ($this->inPostPayProductAttributes === null) {
            /** @var EavAttributeCollection $collection */
            $collection = $this->eavAttributeCollectionFactory->create();
            $collection->addFieldToFilter(EavAttributeInterface::IS_VISIBLE_ON_FRONT, ['eq' => 1]);
            $collection->setEntityTypeFilter($this->productFactory->create()->getTypeId());
            $visibleOnFrontAttributes = [
                'description',
                'short_description',
                'image'
            ];

            foreach ($collection->getItems() as $attribute) {
                if ($attribute instanceof Attribute) {
                    $visibleOnFrontAttributes[] = $attribute->getAttributeCode();
                }
            }

            $customPromoPriceAttribute = $this->omnibusConfigProvider->getCustomProductPromoPriceAttributeCode(
                $storeId
            );
            $lowestPriceAttributeCode = $this->omnibusConfigProvider->getOmnibusProductLowestPriceAttributeCode(
                $storeId
            );

            if ($customPromoPriceAttribute) {
                $visibleOnFrontAttributes[] = $customPromoPriceAttribute;
            }

            if ($lowestPriceAttributeCode) {
                $visibleOnFrontAttributes[] = $lowestPriceAttributeCode;
            }

            $this->inPostPayProductAttributes = array_unique($visibleOnFrontAttributes);
        }

        return $this->inPostPayProductAttributes;
    }
}
