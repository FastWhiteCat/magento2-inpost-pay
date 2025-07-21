<?php

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class ModuleVersion extends Field
{
    public const UNKNOWN_VERSION = 'Unknown';

    /**
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param array $data
     */
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly ReadFactory $readFactory,
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
        return $this->getComposerVersion((string)$this->getModuleName());
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getComposerVersion(string $moduleName): string
    {
        try {
            $path = $this->componentRegistrar->getPath(
                ComponentRegistrar::MODULE,
                $moduleName
            );
            // @phpstan-ignore-next-line
            $directoryRead = $this->readFactory->create((string)$path);
            $composerJsonData = $directoryRead->readFile('composer.json');

            if ($composerJsonData) {
                $data = json_decode($composerJsonData);

                // @phpstan-ignore-next-line
                return !empty($data->version) ? $data->version : __('Unknown')->render();
            }
        } catch (Exception $e) {
            return __('Unknown')->render();
        }

        return __('Unknown')->render();
    }
}
