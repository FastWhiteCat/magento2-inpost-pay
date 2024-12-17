<?php

declare(strict_types=1);

namespace InPost\Restrictions\Block\Adminhtml\RestrictionsRule\Edit\Tab;

use InPost\Restrictions\Api\Data\RestrictionsRuleInterface;
use InPost\Restrictions\Api\Data\RestrictionsRuleInterfaceFactory;
use InPost\Restrictions\Model\RestrictionsRuleRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as FieldsetRenderer;
use Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Conditions as CatalogRuleConditions;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Magento\Rule\Model\Condition\AbstractCondition;

class ConditionsSerialized extends CatalogRuleConditions
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Conditions $conditions
     * @param FieldsetRenderer $rendererFieldset
     * @param RestrictionsRuleRepository $restrictionsRuleRepository
     * @param RestrictionsRuleInterfaceFactory $restrictionsRuleInterfaceFactory
     * @param array $data
     */
    public function __construct(
        Context                                                      $context,
        Registry                                                     $registry,
        FormFactory                                                  $formFactory,
        Conditions                                                   $conditions,
        FieldsetRenderer                                             $rendererFieldset,
        private readonly RestrictionsRuleRepository                  $restrictionsRuleRepository,
        private readonly RestrictionsRuleInterfaceFactory $restrictionsRuleInterfaceFactory,
        array                                                        $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $conditions, $rendererFieldset, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addTabToForm(
        $model,
        $fieldsetId = 'conditions_fieldset',
        $formName = 'inpostrestrictions_rule_form'
    ): Form {
        $restrictionsRule = $this->getCurrentRule();
        $conditionsFieldSetId = $restrictionsRule->getConditionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            sprintf('catalog_rule/promo_catalog/newConditionHtml/form/%s', $conditionsFieldSetId),
            ['form_namespace' => $formName]
        );

        /** @var Form $form */
        $form = $this->_formFactory->create()->setHtmlIdPrefix('rule_');
        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);
        $fieldset = $form->addFieldset($fieldsetId, ['legend' => __('Leave blank for all products.')])
            ->setRenderer($renderer);
        $fieldset->addField(
            'conditions',
            'text',
            [
                'name'           => 'conditions',
                'label'          => __('Conditions'),
                'title'          => __('Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )->setData('rule', $restrictionsRule)->setRenderer($this->_conditions);

        // @phpstan-ignore-next-line
        $form->setValues($restrictionsRule->getData());
        $this->setConditionFormName($restrictionsRule->getConditions(), $formName, $conditionsFieldSetId);

        return $form;
    }

    /**
     * @return RestrictionsRuleInterface
     */
    private function getCurrentRule(): RestrictionsRuleInterface
    {
        $restrictionsRuleId = $this->getRequest()->getParam(RestrictionsRuleInterface::RULE_ID);
        try {
            $restrictionsRuleId = is_scalar($restrictionsRuleId) ? (int)$restrictionsRuleId : 0;
            $restrictionsRule = $this->restrictionsRuleRepository->get($restrictionsRuleId);
        } catch (LocalizedException $e) {
            $restrictionsRule = $this->restrictionsRuleInterfaceFactory->create();
        }

        return $restrictionsRule;
    }

    /**
     * @param Combine $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, string $formName, string $jsFormName): void
    {
        $conditions->setData('form_name', $formName);
        if ($conditions->getConditions()) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
