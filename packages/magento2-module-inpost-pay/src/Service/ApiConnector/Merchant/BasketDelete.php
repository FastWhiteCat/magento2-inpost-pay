<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use Throwable;
use InPost\InPostPay\Api\ApiConnector\Merchant\BasketDeleteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Exception\InPostPayAuthorizationException;
use InPost\InPostPay\Exception\InPostPayBadRequestException;
use InPost\InPostPay\Exception\InPostPayInternalException;
use InPost\InPostPay\Exception\BasketNotFoundException;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class BasketDelete implements BasketDeleteInterface
{
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $basketId
     * @return void
     * @throws InPostPayBadRequestException
     * @throws InPostPayAuthorizationException
     * @throws BasketNotFoundException
     * @throws InPostPayInternalException
     */
    public function execute(string $basketId): void
    {
        try {
            $this->eventManager->dispatch(
                'izi_basket_binding_delete_before',
                [InPostPayQuoteInterface::BASKET_ID => $basketId]
            );

            $this->inPostPayQuoteRepository->delete($this->inPostPayQuoteRepository->getByBasketId($basketId));

            $this->eventManager->dispatch(
                'izi_basket_binding_delete_after',
                [InPostPayQuoteInterface::BASKET_ID => $basketId]
            );
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());

            throw new BasketNotFoundException();
        } catch (InPostPayAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            throw $e;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            throw new InPostPayBadRequestException();
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage());

            throw new InPostPayInternalException();
        }
    }
}
