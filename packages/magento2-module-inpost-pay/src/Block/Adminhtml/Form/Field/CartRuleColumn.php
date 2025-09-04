<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use InPost\InPostPay\Model\Config\Source\SalesRules;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class CartRuleColumn extends Select
{
    /**
     * @param Context $context
     * @param SalesRules $salesRules
     * @param array $data
     */
    public function __construct(
        Context $context,
        private SalesRules $salesRules,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->salesRules->toOptionArray(true));
        }
        return parent::_toHtml();
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return CartRuleColumn
     */
    public function setInputName(string $value): CartRuleColumn
    {
        return $this->setName($value);
    }
}
