<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
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
            $basketBindingApiKey = $inPostPayQuote->getBasketBindingApiKey();

            if (empty($basketBindingApiKey)) {
                $basketBindingApiKey = $this->getBasketBindingApiKey->execute($quoteId);
                $inPostPayQuote->setBasketBindingApiKey($basketBindingApiKey);
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
