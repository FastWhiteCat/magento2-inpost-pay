<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BestsellerProductActions extends Column
{
    public const URL_PATH_EDIT = 'inpostpay/bestsellers/edit';
    public const URL_PATH_DELETE = 'inpostpay/bestsellers/delete';

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
                if (isset($item[InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID => $item[
                                    InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID
                                    ]
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID => $item[
                                    InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID
                                    ]
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __(
                                    'Deleting Bestseller Product "%1"',
                                    (string)($item[InPostPayBestsellerProductInterface::SKU] ?? '')
                                ),
                                'message' => __(
                                    'Are you sure you want to delete "%1" bestseller?',
                                    (string)($item[InPostPayBestsellerProductInterface::SKU] ?? '')
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
