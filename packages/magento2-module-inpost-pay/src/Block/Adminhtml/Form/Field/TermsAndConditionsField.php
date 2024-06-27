<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class TermsAndConditionsField extends AbstractFieldArray
{
    public const MAGENTO_AGREEMENT_ID_FIELD = 'magento_agreement_id';
    public const REQUIREMENT_FIELD = 'requirement';
    public const PARENT_MAGENTO_AGREEMENT_ID_FIELD = 'parent_magento_agreement_id';
    public const AGREEMENT_URL_FIELD = 'agreement_url';
    public const AGREEMENT_NAME_FIELD = 'agreement_name';
    public const LINK_LABEL_FIELD = 'label_link';
    public const ADDITIONAL_LINKS_FIELD = 'additional_consent_links';

    private array $renderer;

    /**
     * @inheritDoc
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn(self::MAGENTO_AGREEMENT_ID_FIELD, [
            'label' => __('Magento Terms and Conditions'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer(TermsAndConditionsColumn::class)
        ]);

        $this->addColumn(self::REQUIREMENT_FIELD, [
            'label' => __('Requirement'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer(TermsAndConditionsRequirementsColumn::class)
        ]);

        $this->addColumn(self::PARENT_MAGENTO_AGREEMENT_ID_FIELD, [
            'label' => __('Parent Terms and Conditions'),
            'class' => 'required-entry',
            'renderer' => $this->getRenderer(ParentTermsAndConditionsColumn::class)
        ]);

        $this->addColumn(self::AGREEMENT_URL_FIELD, [
            'label' => __('Agreement url'),
            'class' => 'required-entry'
        ]);

        $this->addColumn(self::AGREEMENT_NAME_FIELD, [
            'label' => __('Link Label'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = (string)__('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $agreementId = $row->getData(self::MAGENTO_AGREEMENT_ID_FIELD);

        if ($agreementId !== null) {
            $options[
                'option_' . $this->getRenderer(TermsAndConditionsColumn::class)
                    ->calcOptionHash($agreementId) // @phpstan-ignore-line
            ] = 'selected="selected"';
        }

        $parentAgreementId = $row->getData(self::PARENT_MAGENTO_AGREEMENT_ID_FIELD);

        if ($parentAgreementId !== null) {
            $options[
                'option_' . $this->getRenderer(ParentTermsAndConditionsColumn::class)
                    ->calcOptionHash($parentAgreementId) // @phpstan-ignore-line
            ] = 'selected="selected"';
        }

        $requirement = $row->getData(self::REQUIREMENT_FIELD);

        if ($requirement !== null) {
            $options[
                'option_' . $this->getRenderer(TermsAndConditionsRequirementsColumn::class)
                    ->calcOptionHash($requirement) // @phpstan-ignore-line
            ] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @param string $className
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRenderer(string $className): BlockInterface
    {
        if (!isset($this->renderer[$className])) {
            $this->renderer[$className] = $this->getLayout()->createBlock(
                $className,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->renderer[$className];
    }
}
