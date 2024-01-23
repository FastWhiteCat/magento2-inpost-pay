<?php

declare(strict_types=1);

namespace InPost\Restrictions\Service;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

class RestrictionsChecker
{
    private AdapterInterface $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
        $this->connection = $this->resourceConnection->getConnection();
    }

    public function isProductIdRestricted(int $productId, int $restrictionType): bool
    {
        $appliesTo = array_unique([$restrictionType, RestrictionsRuleInterface::APPLIES_TO_BOTH]);
        $table = $this->connection->getTableName(RestrictionsRuleProductInterface::TABLE_NAME);
        $query = $this->connection->select()
            ->from($table, [RestrictionsRuleProductInterface::PRODUCT_ID])
            ->where(sprintf('%s = ?', RestrictionsRuleProductInterface::PRODUCT_ID), $productId)
            ->where(sprintf('%s IN (?)', RestrictionsRuleProductInterface::APPLIES_TO), $appliesTo);

        return count($this->connection->fetchCol($query)) > 0;
    }
}
