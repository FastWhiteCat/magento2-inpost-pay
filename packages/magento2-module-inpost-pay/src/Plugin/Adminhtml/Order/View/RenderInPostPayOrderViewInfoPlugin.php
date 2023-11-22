<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\Adminhtml\Order\View;

use InPost\InPostPay\Block\Adminhtml\Order\OrderViewDeliveryInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class RenderInPostPayOrderViewInfoPlugin
{
    /**
     * @param AbstractOrder $subject
     * @param string $resultHtml
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(AbstractOrder $subject, string $resultHtml): string
    {
        if ($subject->getNameInLayout() === 'order_shipping_view') {
            $infoBlock = $subject->getLayout()->createBlock(OrderViewDeliveryInfo::class);
            $additionalInfoHtml = $infoBlock->toHtml();
            $resultHtml .= $additionalInfoHtml;
        }

        return $resultHtml;
    }
}
