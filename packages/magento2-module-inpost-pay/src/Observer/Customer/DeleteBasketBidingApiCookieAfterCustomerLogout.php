<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Customer;

use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class DeleteBasketBidingApiCookieAfterCustomerLogout implements ObserverInterface
{
    /**
     * @param BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService
     */
    public function __construct(
        private readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService
    ) {
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer): void
    {
        $this->basketBindingApiKeyCookieService->deleteBasketBindingKeyCookie();
    }
}
