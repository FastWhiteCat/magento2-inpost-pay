<?php

declare(strict_types=1);

namespace InPost\Restrictions\Ui\Component\Listing\Column;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use InPost\Restrictions\Model\Source\RestrictionsRule\AppliesTo as AppliesToSource;

class AppliesToOptions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AppliesToSource $appliesToSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly AppliesToSource $appliesToSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item): string
    {
        $appliesTo = (int)($item[RestrictionsRuleInterface::APPLIES_TO] ?? 0);
        $itemAppliesToLabel = '';

        foreach ($this->appliesToSource->toOptionArray() as $option) {
            $optionValue = isset($option['value']) ? (int) $option['value'] : 0;
            $optionLabel = isset($option['label']) ? (string) $option['label'] : '';
            if ($optionValue === $appliesTo) {
                $itemAppliesToLabel = $optionLabel;
                break;
            }
        }

        return $itemAppliesToLabel;
    }
}
