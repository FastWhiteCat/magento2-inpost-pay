<?php
namespace InPost\InPostPay\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;

class InPostPayBlock extends Template
{
    public function __construct(
        Context $context,
        private readonly Session $session,
        private readonly BindingBasket $bindingBasket,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getInPostPayCartData(): array
    {
        $quoteId = $this->session->getQuoteId();
        if (!$quoteId) {
            return [];
        }

        return $this->bindingBasket->checkBinding($quoteId);
    }
}
