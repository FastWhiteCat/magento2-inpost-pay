<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\QuoteToBasket;

use Exception;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PromoCodeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Api\DataTransfer\QuoteToBasketDataTransferInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class QuoteToBasketPromoCodesDataTransfer implements QuoteToBasketDataTransferInterface
{
    private const SALESRULE_TABLE = 'salesrule';
    private const SALESRULE_LABEL_TABLE = 'salesrule_label';
    private const SALESRULE_COUPON_TABLE = 'salesrule_coupon';

    private ?AdapterInterface $connection = null;

    public function __construct(
        private readonly PromoCodeInterfaceFactory $promoCodeFactory,
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface $logger
    ) {
    }

    public function transfer(Quote $quote, BasketInterface $basket): void
    {
        $promoCodes = [];
        $appliedRuleIds = explode(',', (string)$quote->getAppliedRuleIds());

        try {
            $storeId = (int)$quote->getStoreId();
            $promoCodesData = $this->collectSalesRulesData($appliedRuleIds, (string)$quote->getCouponCode(), $storeId);
            foreach ($promoCodesData as $promoCodeData) {
                if ($promoCodeData[PromoCodeInterface::PROMO_CODE_VALUE]) {
                    /** @var PromoCodeInterface $promoCode */
                    $promoCode = $this->promoCodeFactory->create();
                    $promoCode->setPromoCodeValue($promoCodeData[PromoCodeInterface::PROMO_CODE_VALUE]);
                    $promoCode->setName($promoCodeData[PromoCodeInterface::NAME]);
                    $promoCodes[] = $promoCode;
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $basket->setPromoCodes($promoCodes);
    }

    private function collectSalesRulesData(array $appliedRuleIds, string $couponCode, int $storeId): array
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
            sprintf('s.rule_id = sl.rule_id AND sl.store_id = %s', $storeId),
            ['rule_label' => new Zend_Db_Expr('COALESCE(sl.label, s.name)')]
        );

        $query->joinLeft(
            ['sc' => $this->getConnection()->getTableName(self::SALESRULE_COUPON_TABLE)],
            sprintf('s.rule_id = sc.rule_id AND sc.code = \'%s\'', $couponCode),
            ['rule_coupon' => new Zend_Db_Expr('COALESCE(sc.code, \'\')')]
        );

        $query->where('s.rule_id IN (?)', $ruleIds);

        $salesRuleData = [];
        foreach ($this->getConnection()->fetchAll($query) as $row) {
            $salesRuleData[] = [
                'name' => (string)($row['rule_label'] ?? ''),
                'promo_code_value' => !empty($row['rule_coupon']) ? (string)$row['rule_coupon'] : ''
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
