<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class TermsAndConditionsField extends AbstractFieldArray
{
    /** @var BlockInterface */
    private $agreementRenderer;
    /** @var BlockInterface */
    private $requirementsRenderer;

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('magento_agreement_id', [
            'label' => __('Magento Terms and Conditions'),
            'class' => 'required-entry',
            'renderer' => $this->getAgreementRenderer()
        ]);

        $this->addColumn('requirement', [
            'label' => __('Requirement'),
            'class' => 'required-entry',
            'renderer' => $this->getRequirementsRenderer()
        ]);

        $this->addColumn('agreement_url', [
            'label' => __('Agreement url'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
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
        $agreementId = $row->getMagentoAgreementId();
        if ($agreementId !== null) {
            $options['option_' . $this->getAgreementRenderer()->calcOptionHash($agreementId)]
                = 'selected="selected"';
        }

        $requirement = $row->getRequirement();
        if ($requirement !== null) {
            $options['option_' . $this->getRequirementsRenderer()->calcOptionHash($requirement)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    private function getAgreementRenderer(): BlockInterface
    {
        if (!$this->agreementRenderer) {
            $this->agreementRenderer = $this->getRenderer(TermsAndConditionsColumn::class);
        }

        return $this->agreementRenderer;
    }

    private function getRequirementsRenderer(): BlockInterface
    {
        if (!$this->requirementsRenderer) {
            $this->requirementsRenderer = $this->getRenderer(TermsAndConditionsRequirementsColumn::class);
        }

        return $this->requirementsRenderer;
    }

    /**
     * @param string $className
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRenderer(string $className): BlockInterface
    {
        return $this->getLayout()->createBlock(
            $className,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
}
