<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Bestsellers\Edit;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    /**
     * @param Context $context
     */
    public function __construct(
        private readonly Context $context
    ) {
    }

    protected function getRuleId(): ?int
    {
        $ruleId = $this->context->getRequest()->getParam(InPostPayBestsellerProductInterface::BESTSELLER_PRODUCT_ID);

        return (is_scalar($ruleId)) ? (int) $ruleId : null;
    }

    protected function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
