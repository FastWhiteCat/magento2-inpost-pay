<?php

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use Composer\InstalledVersions;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ModuleVersion extends Field
{
    public const UNKNOWN_VERSION = 'Unknown';
    private const PACKAGE_NAME = 'inpost/magento2-module-inpost-pay';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return sprintf('v%s', $this->getVersion());
    }

    /**
     * Get Module version number
     *
     * @return string
     */
    public function getVersion(): string
    {
        try {
            return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)
                ?? __(self::UNKNOWN_VERSION)->render();
        } catch (\Throwable $e) {
            return __(self::UNKNOWN_VERSION)->render();
        }
    }
}
