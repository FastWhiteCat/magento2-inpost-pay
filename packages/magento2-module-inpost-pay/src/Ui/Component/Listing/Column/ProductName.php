<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ProductName extends Column
{
    public const PRODUCT_NAME = 'product_name';

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param UrlInterface $urlBuilder
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
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
                $productNameRow = '';
                if (isset($item[InPostPayBestsellerProductInterface::SKU])) {
                    $product = $this->getProductBySku(
                        (string)$item[InPostPayBestsellerProductInterface::SKU]
                    );

                    if ($product === null) {
                        continue;
                    }

                    $productName = $product->getName();
                    $productEditUrl = $this->getUrl('catalog/product/edit', ['id' => (int)$product->getId()]);
                    $productNameRow = $this->prepareEditLink((string)$productName, $productEditUrl);
                }

                $item[self::PRODUCT_NAME] = $productNameRow;
            }
        }

        return $dataSource;
    }

    /**
     * @param string $sku
     * @return ProductInterface|null
     */
    private function getProductBySku(string $sku): ?ProductInterface
    {
        try {
            return $this->productRepository->get($sku);
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    private function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @param string $productName
     * @param string $productEditUrl
     * @return string
     */
    private function prepareEditLink(string $productName, string $productEditUrl): string
    {
        return sprintf(
            '<a class="action-menu-item" href="%s" target="_blank">%s</a>',
            $productEditUrl,
            $productName
        );
    }
}
