<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterface;
use Magento\Framework\DataObject;

class MobileLink extends DataObject implements MobileLinkInterface
{
    public function getLink(): ?string
    {
        $message = $this->getData(MobileLinkInterface::LINK);
        return (is_scalar($message)) ? (string)$message : null;
    }
}
