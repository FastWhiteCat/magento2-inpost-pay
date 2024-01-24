<?php

declare(strict_types=1);

namespace InPost\Restrictions\Ui\Component\Listing\Column;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class WebsiteOptions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param WebsiteRepositoryInterface $websiteRepositoryInterface
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly WebsiteRepositoryInterface $websiteRepositoryInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
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
     * @throws LocalizedException
     */
    protected function prepareItem(array $item): string
    {
        $websiteId = (int)($item[RestrictionsRuleInterface::WEBSITE_ID] ?? 0);

        return $this->websiteRepositoryInterface->getById($websiteId)->getName();
    }
}
