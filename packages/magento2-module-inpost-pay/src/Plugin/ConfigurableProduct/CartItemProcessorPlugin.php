<?php
declare(strict_types=1);
namespace InPost\InPostPay\Plugin\ConfigurableProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterfaceFactory;
use Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor;
use Magento\Quote\Api\Data\CartItemInterface;

class CartItemProcessorPlugin
{
    /**
     * @param CartItemProcessor $subject
     * @param $result
     * @param CartItemInterface $cartItem
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvertToBuyRequest( //@phpstan-ignore-line
        CartItemProcessor $subject,
        $result,
        CartItemInterface $cartItem
    ): void {
        if ($result
            && $cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()
        ) {
            $result->setData('id', $cartItem->getItemId());
        }
    }
}
