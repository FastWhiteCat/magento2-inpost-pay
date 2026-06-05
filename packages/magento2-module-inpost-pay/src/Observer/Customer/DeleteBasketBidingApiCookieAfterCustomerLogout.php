<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Customer;

use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class DeleteBasketBidingApiCookieAfterCustomerLogout implements ObserverInterface
{
    /**
     * @param BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService
     * @param GeneralConfigProvider $generalConfigProvider
     */
    public function __construct(
        private readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService,
        private readonly GeneralConfigProvider $generalConfigProvider
    ) {
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(EventObserver $observer): void
    {
        if (!$this->canSync()) {
            return;
        }

        $this->basketBindingApiKeyCookieService->deleteBasketBindingKeyCookie();
    }

    private function canSync(): bool
    {
        return $this->generalConfigProvider->isEnabled();
    }
}
