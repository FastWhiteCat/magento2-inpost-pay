<?php
declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

class PromotionsField extends AbstractFieldArray
{
    public const MAGENTO_CART_RULE_ID_FIELD = 'magento_cart_rule_id';
    public const PROMOTION_URL_FIELD = 'promotion_url';
    public const PROMOTIONS_CONFIG_FIELD_ID = 'payment_other_inpost_pay_inpost_pay_promotions_promotions_mapping';
    public const MAX_PROMOTIONS_LIMIT = 5;

    private ?BlockInterface $cartRuleRenderer = null;

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        // Call the default rendering logic
        $html = parent::_getElementHtml($element);
        $html = '<div id="' . self::PROMOTIONS_CONFIG_FIELD_ID . '">' . $html . '</div>';

        $enabledAddButtonText = __('Add another promotion')->render();
        $disabledAddButtonText = __('Promotions limit reached. Please remove one before adding another')->render();

        $html .= '<script>
            require(["InPost_InPostPay/js/system/config/manage-promotions-limit"], function(limitRows){
                limitRows({
                    inputId: "' . self::PROMOTIONS_CONFIG_FIELD_ID . '",
                    maxRowLimit: ' . self::MAX_PROMOTIONS_LIMIT . ',
                    enabledAddButtonText: "' . $enabledAddButtonText . '",
                    disabledAddButtonText: "' . $disabledAddButtonText . '",
                });
            });
        </script>';

        return $html;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(self::MAGENTO_CART_RULE_ID_FIELD, [
            'label' => __('Magento cart rule with coupon'),
            'class' => 'required-entry',
            'renderer' => $this->getCartRuleRenderer()
        ]);

        $this->addColumn(self::PROMOTION_URL_FIELD, [
            'label' => __('Promotion description url'),
            'class' => 'required-entry validate-url',
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add another promotion')->render();
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
        $cartRuleId = $row->getMagentoCartRuleId();
        if ($cartRuleId !== null) {
            // @phpstan-ignore-next-line
            $options['option_' . $this->getCartRuleRenderer()->calcOptionHash($cartRuleId)]
                = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    private function getCartRuleRenderer(): BlockInterface
    {
        if (!$this->cartRuleRenderer) {
            $this->cartRuleRenderer = $this->getRenderer(CartRuleColumn::class);
        }

        return $this->cartRuleRenderer;
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
