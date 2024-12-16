<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Customer;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class CreateOrUpdateBasketBidingApiCookieAfterCustomerLogin implements ObserverInterface
{
    /**
     * @param BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService
     * @param CartRepositoryInterface $cartRepository
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     */
    public function __construct(
        private readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
    ) {
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer): void
    {
        $customer = $observer->getData('customer');

        if (!$customer instanceof Customer) {
            return;
        }

        $quoteId = $this->getCustomerQuoteId($customer);

        if ($quoteId === null) {
            return;
        }

        try {
            $basket = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

            if ($basket->getBasketBindingApiKey()) {
                $this->basketBindingApiKeyCookieService->createOrUpdateBasketBindingCookie(
                    $basket->getBasketBindingApiKey()
                );
            }
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * @param Customer $customer
     * @return int|null
     */
    private function getCustomerQuoteId(Customer $customer): ?int
    {
        try {
            $customerId = is_scalar($customer->getId()) ? (int)$customer->getId() : 0;
            $quote = $this->cartRepository->getForCustomer($customerId);
            $quoteId = (int)$quote->getId();
        } catch (NoSuchEntityException $e) {
            $quoteId = null;
        }

        return $quoteId && is_scalar($quoteId) ? (int)$quoteId : null;
    }
}
