<?php

declare(strict_types=1);

namespace InPost\Restrictions\Service;

use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

class RestrictionRuleProductMassActionService
{
    private const CHUNK_SIZE = 200;

    private AdapterInterface $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @param int[] $ruleIds
     * @return void
     */
    public function massDeleteRuleProductsByRuleIds(array $ruleIds): void
    {
        $this->connection->delete(
            $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME),
            [sprintf('%s IN (?)', RestrictionsRuleProductInterface::RULE_ID) => $ruleIds]
        );
    }

    /**
     * @param array $ruleProductsData
     * @return void
     */
    public function massCreateRuleProducts(array $ruleProductsData): void
    {
        foreach (array_chunk($ruleProductsData, self::CHUNK_SIZE) as $ruleProductsDataChunk) {
            $this->connection->insertMultiple(
                $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME),
                $ruleProductsDataChunk
            );
        }
    }
}
