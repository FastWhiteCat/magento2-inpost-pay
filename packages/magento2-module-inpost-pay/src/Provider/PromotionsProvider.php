<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider;

use DateTime;
use DateTimeZone;
use InPost\InPostPay\Api\Data\Merchant\BasketInterface;
use InPost\InPostPay\Block\Adminhtml\Form\Field\PromotionsField;
use InPost\InPostPay\Model\Cache\Promotions\Type as PromotionsCacheType;
use InPost\InPostPay\Provider\Config\PromotionsMappingConfigProvider;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\SalesRule\Model\Data\Rule;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as SalesRuleCollectionFactory;
use Magento\SalesRule\Model\Rule as RuleModel;


class PromotionsProvider
{
    private const PROMOTION_DESCRIPTION_MAX_LENGTH = 60;

    /**
     * @param PromotionsMappingConfigProvider $promotionsMappingConfigProvider
     * @param SalesRuleCollectionFactory $salesRuleCollectionFactory
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly PromotionsMappingConfigProvider $promotionsMappingConfigProvider,
        private readonly SalesRuleCollectionFactory $salesRuleCollectionFactory,
        private readonly SerializerInterface $serializer,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @param int $customerGroupId
     * @return array
     */
    public function getPromotions(int $customerGroupId): array
    {
        if (!$this->promotionsMappingConfigProvider->isPromotionsEnabled()) {
            return [];
        }

        $promotions = $this->cache->load(PromotionsCacheType::TYPE_IDENTIFIER . '_' . $customerGroupId);

        if (empty($promotions)) {
            $promotionsMapping = $this->promotionsMappingConfigProvider->getPromotionsMapping();
            if (!$promotionsMapping) {
                return [];
            }

            $ids = array_column($promotionsMapping, PromotionsField::MAGENTO_CART_RULE_ID_FIELD);
            $promotionsArray = $this->getPromotionsList($ids, $customerGroupId);
            $promotions = [];
            foreach ($promotionsMapping as $item) {
                if (!isset($promotionsArray[$item[PromotionsField::MAGENTO_CART_RULE_ID_FIELD]])) {
                    continue;
                }

                /** @var RuleModel $salesRule */
                $salesRule = $promotionsArray[$item[PromotionsField::MAGENTO_CART_RULE_ID_FIELD]];

                if ($salesRule->getUsesPerCoupon() && $salesRule->getTimesUsed() >= $salesRule->getUsesPerCoupon()) {
                    continue;
                }

                $fromDate = $salesRule->getFromDate() ? $this->formatInPostDate($salesRule->getFromDate()) : '';
                $toDate = $salesRule->getToDate() ? $this->formatInPostDate($salesRule->getToDate()) : '';

                $promotions[] = [
                    'type' => 'MERCHANT',
                    'promo_code_value' => $salesRule->getCode(),
                    'description' => substr(
                        $salesRule->getDescription(),
                        0,
                        self::PROMOTION_DESCRIPTION_MAX_LENGTH
                    ),
                    'start_date' => $fromDate,
                    'end_date' => $toDate,
                    'priority' => $salesRule->getSortOrder(),
                    'details' => [
                        'link' => $item[PromotionsField::PROMOTION_URL_FIELD]
                    ]
                ];
            }

            $encodedPromotionsData = (string)$this->serializer->serialize($promotions);
            $this->cache->save(
                $encodedPromotionsData,
                PromotionsCacheType::TYPE_IDENTIFIER . '_' . $customerGroupId,
                [PromotionsCacheType::CACHE_TAG],
                PromotionsCacheType::TTL
            );
        }

        /** @phpstan-ignore-next-line */
        return is_array($promotions) ? $promotions : (array)$this->serializer->unserialize($promotions);
    }

    /**
     * @param array $ids
     * @param int $customerGroupId
     * @return array
     */
    private function getPromotionsList(array $ids, int $customerGroupId): array
    {
        $salesRuleCollection = $this->salesRuleCollectionFactory->create();
        $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $currentDateTime = $currentDateTime->format('Y-m-d');

        $salesRuleCollection
            ->addFieldToFilter(Rule::KEY_IS_ACTIVE, 1)
            ->addFieldToFilter(Rule::KEY_COUPON_TYPE, ['eq' => RuleModel::COUPON_TYPE_SPECIFIC])
            ->addFieldToFilter(Rule::KEY_RULE_ID, ['in' => $ids])
            ->addFieldToFilter(Rule::KEY_FROM_DATE, [['lteq' => $currentDateTime], ['null' => true]])
            ->addFieldToFilter(Rule::KEY_TO_DATE, [['gteq' => $currentDateTime], ['null' => true]])
            ->addCustomerGroupFilter($customerGroupId);

        $salesRuleCollection->addOrder(Rule::KEY_SORT_ORDER, Collection::SORT_ORDER_ASC);
        $salesRuleCollection->load();
        $salesRules = [];

        foreach ($salesRuleCollection->getItems() as $salesRule) {
            if ($salesRule instanceof RuleModel) {
                $salesRules[$salesRule->getRuleId()] = $salesRule;
            }
        }

        return $salesRules;
    }

    /**
     * @param string $date
     * @return string
     */
    private function formatInPostDate(string $date): string
    {
        return (new DateTime($date))->format(BasketInterface::INPOST_DATE_FORMAT);
    }
}
