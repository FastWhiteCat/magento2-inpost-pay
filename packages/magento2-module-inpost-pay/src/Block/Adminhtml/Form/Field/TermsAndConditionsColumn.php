<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use InPost\InPostPay\Model\Config\Source\TermsAndConditions;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class TermsAndConditionsColumn extends Select
{
    /**
     * @param Context $context
     * @param TermsAndConditions $termsAndConditions
     * @param array $data
     */
    public function __construct(
        Context $context,
        private TermsAndConditions $termsAndConditions,
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
            $this->setOptions($this->termsAndConditions->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return TermsAndConditionsColumn
     */
    public function setInputName(string $value): TermsAndConditionsColumn
    {
        return $this->setName($value);
    }
}
