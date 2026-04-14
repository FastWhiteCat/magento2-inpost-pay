<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use Composer\InstalledVersions;
use Exception;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ModuleVersion extends Field
{
    public const UNKNOWN_VERSION = 'Unknown';

    /**
     * Return element html
     *
     * @param AbstractElement $element
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
        return $this->getComposerVersion('inpost/magento2-module-inpost-pay');
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getComposerVersion(string $moduleName): string
    {
        try {
            return InstalledVersions::getVersion($moduleName);
        } catch (Exception $e) {
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
        }

        return __(self::UNKNOWN_VERSION)->render();
    }
}
