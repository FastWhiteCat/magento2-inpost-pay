<?php
declare(strict_types=1);

namespace InPost\InPostPay\Provider\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class PromotionsMappingConfigProvider
{
    private const XML_PATH_PROMOTIONS_ENABLED = 'payment/inpost_pay/promotions_enabled';
    private const XML_PATH_PROMOTIONS_MAPPING = 'payment/inpost_pay/promotions_mapping';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Returns mapped terms and conditions
     *
     * @return bool
     */
    public function isPromotionsEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PROMOTIONS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns mapped terms and conditions
     *
     * @return array
     */
    public function getPromotionsMapping(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PROMOTIONS_MAPPING,
            ScopeInterface::SCOPE_STORE
        );

        $promotions = null;
        if (is_scalar($value)) {
            $promotions = $this->serializer->unserialize((string)$value);
        }

        return is_array($promotions) ? $promotions : [];
    }
}
