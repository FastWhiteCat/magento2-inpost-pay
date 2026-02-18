<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Config\Backend;

use InPost\InPostPay\Enum\InPostDeliveryOption;
use InPost\InPostPay\Enum\InPostDeliveryType;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use InPost\InPostPay\Registry\ShippingMethodMappingRegistry;

class UniqueShippingMethodValidator extends Value
{
    private const REGISTRY_KEY_PREFIX = 'inpost_pay_shipping_method_';
    private const INPOST_PAY_SHIPPING_METHOD_MAPPING_CONFIG_PATH_PATTERN = 'payment/inpost_pay/inpost_%s_%s_mapping';
    private const INPOST_PAY_SHIPPING_METHOD_TYPES = [
        InPostDeliveryType::APM_VALUE,
        InPostDeliveryType::COURIER_VALUE
    ];

    private const INPOST_PAY_SHIPPING_METHOD_OPTIONS = [
        InPostDeliveryOption::PWW_VALUE,
        InPostDeliveryOption::COD_VALUE,
        InPostDeliveryOption::CODPWW_VALUE
    ];

    /**
     * @param ShippingMethodMappingRegistry $shippingMethodRegistry
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        private readonly ShippingMethodMappingRegistry $shippingMethodRegistry,
        private readonly ScopeConfigInterface $scopeConfig,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate uniqueness of shipping method mappings before saving
     *
     * @return AbstractModel
     * @throws LocalizedException
     */
    public function beforeSave(): AbstractModel
    {
        $value = $this->getValue();

        if (empty($value) || !is_string($value)) {
            return parent::beforeSave();
        }

        $scope = $this->getScope();
        $scopeId = $this->getScopeId();
        $currentPath = $this->getPath();
        $registryKeyBase = self::REGISTRY_KEY_PREFIX . $scope . '_' . $scopeId . '_';
        $registryKey = $registryKeyBase . $currentPath;

        if ($this->shippingMethodRegistry->valueExistsForOtherKeys($registryKey, $value)) {
            $duplicatePath = $this->shippingMethodRegistry->getKeyForValue($registryKey, $value);
            $duplicatePath = str_replace($registryKeyBase, '', (string)$duplicatePath);

            throw new LocalizedException(
                __(
                    'Shipping method "%1" is already used in "%2" configuration. Each method can only be used once.',
                    $value,
                    $duplicatePath
                )
            );
        }

        $this->shippingMethodRegistry->register($registryKey, $value);
        $values = [];

        foreach ($this->getConfigPaths() as $path) {
            if ($path === $currentPath) {
                continue;
            }

            $configValue = $this->scopeConfig->getValue(
                $path,
                $scope,
                (int)$scopeId
            );

            if (!empty($configValue)) {
                $values[$path] = $configValue;
            }
        }

        if (in_array($value, $values, true)) {
            $duplicatePath = array_search($value, $values, true);

            throw new LocalizedException(
                __(
                    'Shipping method "%1" is already used in "%2" configuration. Each method can only be used once.',
                    $value,
                    $duplicatePath
                )
            );
        }

        return parent::beforeSave();
    }

    /**
     * @return array
     */
    private function getConfigPaths(): array
    {
        $configPaths = [];

        foreach (self::INPOST_PAY_SHIPPING_METHOD_TYPES as $shippingMethodType) {
            foreach (self::INPOST_PAY_SHIPPING_METHOD_OPTIONS as $shippingMethodOption) {
                $configPaths[] = sprintf(
                    self::INPOST_PAY_SHIPPING_METHOD_MAPPING_CONFIG_PATH_PATTERN,
                    strtolower($shippingMethodType),
                    strtolower($shippingMethodOption)
                );
            }
        }

        return $configPaths;
    }
}
