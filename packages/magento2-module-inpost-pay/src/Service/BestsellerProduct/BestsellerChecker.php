<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\BestsellerProduct;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Provider\Config\BestsellersCronConfigProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class BestsellerChecker
{
    private ?AdapterInterface $connection = null;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly BestsellersCronConfigProvider $bestsellersCronConfigProvider
    ) {
    }

    public function isSynchronizationEnabled(?int $storeId = null): bool
    {
        return $this->bestsellersCronConfigProvider->isSynchronizationEnabled($storeId);
    }

    public function isBestsellerProductBySku(string $sku, ?int $websiteId = null): bool
    {
        $tableName = $this->getConnection()->getTableName(InPostPayBestsellerProductInterface::TABLE_NAME);

        $select = $this->getConnection()->select()
            ->from($tableName, ['sku'])
            ->where('sku = ?', $sku);

        if ($websiteId !== null) {
            $select->where('website_id = ?', $websiteId);
        }

        $result = $this->getConnection()->fetchOne($select);

        return (bool)$result;
    }

    public function isBestsellerProductById(int $entityId, ?int $websiteId = null): bool
    {
        $bestsellerTable = $this->getConnection()->getTableName('inpost_pay_bestseller_product');
        $catalogTable = $this->getConnection()->getTableName('catalog_product_entity');

        $select = $this->getConnection()->select()
            ->from(['bp' => $bestsellerTable], ['bp.sku'])
            ->join(
                ['cpe' => $catalogTable],
                'bp.sku = cpe.sku',
                []
            )
            ->where('cpe.entity_id = ?', $entityId);

        if ($websiteId !== null) {
            $select->where('bp.website_id = ?', $websiteId);
        }

        $result = $this->getConnection()->fetchOne($select);

        return (bool)$result;
    }

    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
