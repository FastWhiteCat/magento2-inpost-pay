<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use InPost\InPostPay\Provider\MagentoModuleVersionProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ModuleVersion extends Field
{
    public function __construct(
        private readonly MagentoModuleVersionProvider $versionProvider,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element): string
    {
        return sprintf('v%s', $this->versionProvider->getVersion());
    }
}
