<?php

declare(strict_types=1);

namespace InPost\Restrictions\Ui\Component\Listing\Column;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RestrictionsRuleActions extends Column
{
    public const URL_PATH_EDIT = 'inpostrestrictions/rule/edit';
    public const URL_PATH_DELETE = 'inpostrestrictions/rule/delete';

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare actions as data source for grid
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[RestrictionsRuleInterface::RULE_ID])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    RestrictionsRuleInterface::RULE_ID => $item[
                                        RestrictionsRuleInterface::RULE_ID
                                    ]
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    RestrictionsRuleInterface::RULE_ID => $item[
                                        RestrictionsRuleInterface::RULE_ID
                                    ]
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Deleting Rule "%1"', (string)($item['name'] ?? '')),
                                'message' => __(
                                    'Are you sure you want to delete "%1" rule?',
                                    (string)($item['name'] ?? '')
                                )
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
