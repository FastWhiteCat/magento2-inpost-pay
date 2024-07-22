<?php
declare(strict_types=1);
namespace InPost\InPostPay\Plugin\ConfigurableProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Summary\NoticeInterfaceFactory;
use Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor;
use Magento\Quote\Api\Data\CartItemInterface;

class CartItemProcessorPlugin
{
    public function afterConvertToBuyRequest(CartItemProcessor $subject, $result, CartItemInterface $cartItem): void
    {
        if ($result
            && $cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()
        ) {
            $result->setData('id', $cartItem->getItemId());
        }
    }
}
