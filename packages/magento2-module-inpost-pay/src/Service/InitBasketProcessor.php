<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Provider\Cart\Session\CartSessionCookieProvider;
use InPost\InPostPay\Service\ApiConnector\GetBasketBindingApiKey;
use InPost\InPostPay\Service\Cart\BasketBindingApiKeyCookieService;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class InitBasketProcessor
{
    public function __construct(
        private readonly GetBasketBindingApiKey $getBasketBindingApiKey,
        private readonly GetBasketId $getBasketId,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly BasketBindingApiKeyCookieService $basketBindingApiKeyCookieService,
        private readonly CartSessionCookieProvider $cartSessionCookieProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @param string|null $gaClientId
     * @param string|null $fbclid
     * @param string|null $gclid
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    public function process(
        int $quoteId,
        ?string $gaClientId = null,
        ?string $fbclid = null,
        ?string $gclid = null
    ): InPostPayQuoteInterface {
        try {
            $basketId = $this->getBasketId->get(
                $quoteId,
                true,
                $gaClientId,
                $fbclid,
                $gclid
            );
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId((string)$basketId);
            $basketBindingApiKey = $inPostPayQuote->getBasketBindingApiKey();
            $saveRequired = false;

            if (empty($basketBindingApiKey)) {
                $basketBindingApiKey = $this->getBasketBindingApiKey->execute($quoteId);
                $inPostPayQuote->setBasketBindingApiKey($basketBindingApiKey);
                $saveRequired = true;
            }

            $cookieSession = $this->cartSessionCookieProvider->getCookieSession();

            if ($cookieSession !== $inPostPayQuote->getSessionCookie()) {
                $inPostPayQuote->setSessionCookie($cookieSession);
                $saveRequired = true;
            }

            if ($saveRequired) {
                $this->inPostPayQuoteRepository->save($inPostPayQuote);
            }

            $this->basketBindingApiKeyCookieService->createOrUpdateBasketBindingCookie($basketBindingApiKey);
        } catch (CouldNotSaveException | NoSuchEntityException | LocalizedException $e) {
            $errorMessage = __('Could not initiate InPost Pay Quote. Reason: %1', $e->getMessage());
            $this->logger->error($errorMessage->getText());

            throw new LocalizedException($errorMessage);
        }

        return $inPostPayQuote;
    }
}
