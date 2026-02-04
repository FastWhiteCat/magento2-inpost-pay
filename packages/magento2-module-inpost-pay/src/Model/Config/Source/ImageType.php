<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Source;

use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ImageType implements OptionSourceInterface
{
    public function __construct(
        private readonly Config $mediaConfig,
        private readonly Repository $productAttributeRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function toOptionArray(): array
    {
        $attributeCodes = $this->mediaConfig->getMediaAttributeCodes();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('attribute_code', $attributeCodes, 'in')
            ->create();
        $attributes = $this->productAttributeRepository->getList($searchCriteria);
        $options = [];
        foreach ($attributes->getItems() as $attribute) {
            $options[] = [
                'label' => $attribute->getDefaultFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $options;
    }
}
