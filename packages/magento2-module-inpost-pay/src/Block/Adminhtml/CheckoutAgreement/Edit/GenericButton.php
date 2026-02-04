<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\CheckoutAgreement\Edit;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
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

    protected function getAgreementId(): ?int
    {
        $agreementId = $this->context->getRequest()->getParam(InPostPayCheckoutAgreementInterface::AGREEMENT_ID);

        return (is_scalar($agreementId)) ? (int) $agreementId : null;
    }

    protected function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
