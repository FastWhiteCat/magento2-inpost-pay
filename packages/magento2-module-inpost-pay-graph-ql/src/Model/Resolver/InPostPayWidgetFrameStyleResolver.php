<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Provider\Config\LayoutConfigProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

class InPostPayWidgetFrameStyleResolver implements ResolverInterface
{
    /**
     * @param LayoutConfigProvider $layoutConfigProvider
     */
    public function __construct(
        private readonly LayoutConfigProvider $layoutConfigProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): string
    {
        $websiteId = null;
        $store = $context->getExtensionAttributes()->getStore();
        if ($store instanceof StoreInterface) {
            $websiteId = (int)$store->getWebsiteId();
        }

        return $this->layoutConfigProvider->getWidgetStyles($websiteId);
    }
}
