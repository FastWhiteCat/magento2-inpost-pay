<?php
declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\Widget\Basket\GetMobileLinkInterface;
use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterface;
use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterfaceFactory;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class GetMobileLink implements GetMobileLinkInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly MobileLinkInterfaceFactory $mobileLinkInterfaceFactory,
        private readonly BindingBasket $bindingBasket,
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(int $cartId): MobileLinkInterface
    {
        $data = [];
        try {
            $cart   = $this->cartRepository->getActive($cartId);
            $result = $this->bindingBasket->checkBinding((int)$cart->getId());

            if (isset($result['inpost_basket_id'])) {
                if ($this->sandboxConfigProvider->isSandboxEnabled()) {
                    $link = 'inpost://izilinksandbox?basket_id=' . $result['inpost_basket_id'];
                } else {
                    $link = 'inpost://izilink?basket_id=' . $result['inpost_basket_id'];
                }

                $data = ['link' => $link];
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        return $this->mobileLinkInterfaceFactory->create(['data' => $data]);
    }
}
