<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Quote;

use Exception;
use InPost\InPostPay\Api\InPostPayLockerIdProviderInterface;
use InPost\InPostPay\Api\InPostPayQuoteInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

class InPostPayQuote implements InPostPayQuoteInterface
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function setLockerIdForCart(CartInterface $cart, string $lockerId): void
    {
        try {
            $cart->setData(InPostPayLockerIdProviderInterface::INPOST_PAY_LOCKER_ID_FIELD, $lockerId);
            $this->cartRepository->save($cart);
            $this->logger->info(
                sprintf('Successfully saved Locker %s for Cart ID %s.', $lockerId, (int)$cart->getId())
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'Could not save Locker "%1" on Cart ID %2. Reason: %3',
                    $lockerId,
                    (int)$cart->getId(),
                    $e->getMessage()
                )
            );
        }
    }
}
