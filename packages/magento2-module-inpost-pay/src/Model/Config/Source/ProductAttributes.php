<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;

class ProductAttributes implements OptionSourceInterface
{
    public const NOT_SELECTED_ATTRIBUTE_VALUE = 'none';
    private const EAV_ATTRIBUTE_PRODUCT_TYPE_ID = 4;
    private array $nonScalarAttributesFrontendInputs = [
        'date',
        'gallery',
        'media_image',
        'multiselect',
        'select',
        'boolean',
        'weight'
    ];

    /**
     * @param AttributeCollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        private readonly AttributeCollectionFactory $attributeCollectionFactory
    ) {
    }

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        $productAttributes = $this->getProductAttributes();
        $attributeOptions = [];
        $attributeOptions[] = [
            'value' => self::NOT_SELECTED_ATTRIBUTE_VALUE,
            'label' => __('None')
        ];

        foreach ($productAttributes as $attribute) {
            $label = (string)$attribute->getDefaultFrontendLabel();
            $value = (string)$attribute->getAttributeCode();
            $attributeOptions[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $attributeOptions;
    }

    /**
     * @return AttributeInterface[]
     */
    private function getProductAttributes(): array
    {
        /** @var AttributeCollection $attributeCollection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addFieldToFilter(
            AttributeInterface::ENTITY_TYPE_ID,
            ['eq' => self::EAV_ATTRIBUTE_PRODUCT_TYPE_ID]
        );
        $attributeCollection->addFieldToFilter(
            AttributeInterface::FRONTEND_INPUT,
            ['nin' => $this->nonScalarAttributesFrontendInputs]
        );
        $attributeCollection->addOrder(AttributeInterface::ATTRIBUTE_CODE, Collection::SORT_ORDER_ASC);
        $attributes = [];

        foreach ($attributeCollection->getItems() as $attribute) {
            if ($attribute instanceof AttributeInterface) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }
}
