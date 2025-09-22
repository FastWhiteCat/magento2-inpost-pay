<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Quote;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\BasketBindingDelete;
use InPost\InPostPay\Enum\InPostBasketStatus;
use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use InPost\InPostPay\Provider\Cart\Session\CartSessionCookieProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class UpdateInPostBasketBeforeQuoteMergeEventObserver implements ObserverInterface
{
    /**
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param BasketBindingDelete $basketBindingDelete
     * @param BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService
     * @param CartSessionCookieProvider $cartSessionCookieProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly BasketBindingDelete $basketBindingDelete,
        private readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService,
        private readonly CartSessionCookieProvider $cartSessionCookieProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $customerQuote = $observer->getEvent()->getData('quote');
        $guestQuote = $observer->getEvent()->getData('source');

        if ($customerQuote instanceof Quote && $guestQuote instanceof Quote) {
            try {
                $this->resolveInPostPayQuotesMerge($customerQuote, $guestQuote);
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }

    /**
     * @param Quote $customerQuote
     * @param Quote $guestQuote
     * @return void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function resolveInPostPayQuotesMerge(Quote $customerQuote, Quote $guestQuote): void
    {
        /** @phpstan-ignore-next-line */
        $customerQuoteId = (int)$customerQuote->getId();
        /** @phpstan-ignore-next-line */
        $guestQuoteId = (int)$guestQuote->getId();
        $customerBasket = $this->getInPostPayQuoteByQuoteId($customerQuoteId);
        $guestBasket = $this->getInPostPayQuoteByQuoteId($guestQuoteId);

        if ($guestBasket && $customerQuoteId && $this->isBasketBound($guestBasket)) {
            $guestBasket->setQuoteId($customerQuoteId);
            $finalBasket = $guestBasket;
            $deprecatedBasket = $customerBasket;
        } elseif ($this->isBasketBound($customerBasket)) {
            $finalBasket = $customerBasket;
            $deprecatedBasket = $guestBasket;
        } else {
            throw new NoSuchEntityException(__('Non of the quotes are InPost Pay bound baskets.'));
        }

        if (isset($deprecatedBasket)) {
            $this->basketBindingDelete->execute($deprecatedBasket->getBasketId());
            $guestQuote->setData(UpdateInPostBasketEventObserver::SKIP_INPOST_PAY_SYNC_FLAG, true);
            $this->inPostPayQuoteRepository->delete($deprecatedBasket);
        }

        if ($finalBasket) {
            $finalBasket->setCartVersion(uniqid());
            $finalBasket->setSessionCookie($this->cartSessionCookieProvider->getCookieSession());
            $this->inPostPayQuoteRepository->save($finalBasket);
            $this->basketBindingApiKeyCookieService->createOrUpdateBasketBindingCookie(
                (string)$finalBasket->getBasketBindingApiKey()
            );
        }
    }

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface|null
     */
    private function getInPostPayQuoteByQuoteId(int $quoteId): ?InPostPayQuoteInterface
    {
        try {
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
        } catch (NoSuchEntityException $e) {
            $inPostPayQuote = null;
        }

        return $inPostPayQuote;
    }

    private function isBasketBound(?InPostPayQuoteInterface $basket = null): bool
    {
        if (!$basket instanceof InPostPayQuoteInterface) {
            return false;
        }

        return $basket->getStatus() === InPostBasketStatus::SUCCESS->value;
    }
}
