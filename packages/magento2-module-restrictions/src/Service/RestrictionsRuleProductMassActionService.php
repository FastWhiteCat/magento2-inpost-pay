<?php

declare(strict_types=1);

namespace InPost\Restrictions\Service;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

class RestrictionsRuleProductMassActionService
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
     * @return int[]
     */
    public function getRuleProductsByRuleIds(array $ruleIds): array
    {
        $table = $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME);
        $query = $this->connection->select()
            ->from($table, [RestrictionsRuleProductInterface::PRODUCT_ID])
            ->where(sprintf('%s IN (?)', RestrictionsRuleProductInterface::RULE_ID), $ruleIds)
            ->distinct();

        return $this->connection->fetchCol($query);
    }

    /**
     * @param int $websiteId
     * @param int $appliesTo
     * @return int[]
     */
    public function getRuleProductsByWebsiteId(int $websiteId, int $appliesTo): array
    {
        $appliesTo = array_unique([$appliesTo, RestrictionsRuleInterface::APPLIES_TO_BOTH]);
        $table = $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME);
        $query = $this->connection->select()
            ->from($table, [RestrictionsRuleProductInterface::PRODUCT_ID])
            ->where(sprintf('%s IN (?)', RestrictionsRuleProductInterface::WEBSITE_ID), $websiteId)
            ->where(sprintf('%s IN (?)', RestrictionsRuleProductInterface::APPLIES_TO), $appliesTo)
            ->distinct();

        return $this->connection->fetchCol($query);
    }

    /**
     * @param int[] $ruleIds
     * @return void
     */
    public function massDeleteRuleProductsByRuleIds(array $ruleIds): void
    {
        $table = $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME);
        $this->connection->delete(
            $table,
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
