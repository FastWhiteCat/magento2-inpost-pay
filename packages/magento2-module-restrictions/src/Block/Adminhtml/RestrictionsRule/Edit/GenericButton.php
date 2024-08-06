<?php

declare(strict_types=1);

namespace InPost\Restrictions\Block\Adminhtml\RestrictionsRule\Edit;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
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
        $ruleId = $this->context->getRequest()->getParam(RestrictionsRuleInterface::RULE_ID);

        return (is_scalar($ruleId)) ? (int) $ruleId : null;
    }

    protected function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
