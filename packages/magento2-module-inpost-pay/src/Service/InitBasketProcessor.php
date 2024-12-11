<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Provider\Cart\Session\CartSessionCookieProvider;
use InPost\InPostPay\Service\ApiConnector\GetBasketBindingApiKey;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class InitBasketProcessor
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly GetBasketBindingApiKey $getBasketBindingApiKey,
        private readonly GetBasketId $getBasketId,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartSessionCookieProvider $cartSessionCookieProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param int $quoteId
     * @return InPostPayQuoteInterface
     * @throws LocalizedException
     */
    public function process(int $quoteId): InPostPayQuoteInterface
    {
        try {
            $basketId = $this->getBasketId->get($quoteId, true);
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId((string)$basketId);
            $saveRequired = false;

            if (empty($inPostPayQuote->getBasketBindingApiKey())) {
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
        } catch (CouldNotSaveException | NoSuchEntityException | LocalizedException $e) {
            $errorMessage = __('Could not initiate InPost Pay Quote. Reason: %1', $e->getMessage());
            $this->logger->error($errorMessage->getText());

            throw new LocalizedException($errorMessage);
        }

        return $inPostPayQuote;
    }
}
