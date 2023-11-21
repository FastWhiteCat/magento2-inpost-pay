<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use InPost\InPostPay\Model\Config\Source\TermsAndConditionsRequirements;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class TermsAndConditionsRequirementsColumn extends Select
{
    public function __construct(
        Context $context,
        private TermsAndConditionsRequirements $requirements,
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
            $this->setOptions($this->requirements->toOptionArray());
        }
        return parent::_toHtml();
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return TermsAndConditionsRequirementsColumn
     */
    public function setInputName(string $value): TermsAndConditionsRequirementsColumn
    {
        return $this->setName($value);
    }
}
