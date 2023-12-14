<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\BasketDeleteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class BasketDelete implements BasketDeleteInterface
{
    private const REQUEST_PREFIX = 'BASKET_DELETE_REQUEST';

    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(string $basketId): void
    {
        $this->eventManager->dispatch('izi_basket_binding_delete_before', ['basketId' => $basketId]);
        $this->createRequestDebugLog(sprintf('Deleting Basket ID: %s', $basketId));

        try {
            $this->inPostPayQuoteRepository->delete($this->inPostPayQuoteRepository->getByInPostBasketId($basketId));
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('An error occurred during delete process. Check error logs'));
        }

        $this->eventManager->dispatch('izi_basket_binding_delete_after', ['basketId' => $basketId]);
        $this->createRequestDebugLog(sprintf('Deleted Basket ID: %s', $basketId));
    }

    private function createRequestDebugLog(string $message): void
    {
        $this->logger->debug(sprintf('%s: %s', self::REQUEST_PREFIX, $message));
    }
}
