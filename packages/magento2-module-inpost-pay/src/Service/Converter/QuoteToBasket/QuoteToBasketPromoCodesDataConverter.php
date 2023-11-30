<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter\QuoteToBasket;

use Exception;
use InPost\InPostPay\Api\Data\Converter\QuoteToBasketDataConverterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class QuoteToBasketPromoCodesDataConverter implements QuoteToBasketDataConverterInterface
{
    private const SALESRULE_TABLE = 'salesrule';
    private const SALESRULE_LABEL_TABLE = 'salesrule_label';
    private const SALESRULE_COUPON_TABLE = 'salesrule_coupon';

    private ?AdapterInterface $connection = null;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function convert(Quote $quote): array
    {
        $promoCodesData = [];
        $appliedRuleIds = explode(',', (string)$quote->getAppliedRuleIds());

        try {
            $storeId = $quote->getStoreId() ?? 0;
            $promoCodesData = $this->collectSalesRulesData($appliedRuleIds, (string)$quote->getCouponCode(), $storeId);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $promoCodesData;
    }

    private function collectSalesRulesData(array $appliedRuleIds, string $couponCode): array
    {
        $ruleIds = [];
        foreach ($appliedRuleIds as $appliedRuleId) {
            if (is_scalar($appliedRuleId)) {
                $ruleIds[] = (int)$appliedRuleId;
            }
        }

        if (empty($ruleIds)) {
            return [];
        }

        $query = $this->getConnection()->select()->from(
            ['s' => $this->getConnection()->getTableName(self::SALESRULE_TABLE)],
            []
        );

        $query->joinLeft(
            ['sl' => $this->getConnection()->getTableName(self::SALESRULE_LABEL_TABLE)],
            's.rule_id = sl.rule_id',
            ['rule_label' => new Zend_Db_Expr('COALESCE(sl.label, s.name)')]
        );

        $query->joinLeft(
            ['sc' => $this->getConnection()->getTableName(self::SALESRULE_COUPON_TABLE)],
            sprintf('s.rule_id = sc.rule_id AND sc.code = \'%s\'', $couponCode),
            ['rule_coupon' => new Zend_Db_Expr('COALESCE(sc.code, \'\')')]
        );

        $query->where('s.rule_id IN (?)', $ruleIds);

        $salesRuleData = [];
        $noCode = __('No Coupon is required.')->render();
        foreach ($this->getConnection()->fetchAll($query) as $row) {
            $salesRuleData[] = [
                'name' => (string)($row['rule_label'] ?? ''),
                'promo_code_value' => !empty($row['rule_coupon']) ? (string)$row['rule_coupon'] : $noCode
            ];
        }

        return $salesRuleData;
    }

    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
