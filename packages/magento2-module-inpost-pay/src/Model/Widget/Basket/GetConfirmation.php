<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterfaceFactory;
use InPost\InPostPay\Api\Widget\Basket\GetConfirmationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Monolog\Logger;

class GetConfirmation implements GetConfirmationInterface
{
    /**
     * @param CartRepositoryInterface           $cartRepository
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param ConfirmationInterfaceFactory      $confirmationInterfaceFactory
     * @param Logger                            $logger
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly ConfirmationInterfaceFactory $confirmationInterfaceFactory,
        private readonly Logger $logger
    ) {
    }

    public function execute(int $cartId): ConfirmationInterface
    {
        try {
            $cart   = $this->cartRepository->get($cartId);
            $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId((int)$cart->getId());

            $data = [
                'message'             => $this->getProperMessage($inpostPayQuote->getStatus())->render(),
                'status'              => $inpostPayQuote->getStatus() ?: '0',
                'browser_id'          => $inpostPayQuote->getBrowserId(),
                'browser_trusted'     => $inpostPayQuote->getBrowserTrusted(),
                'name'                => $inpostPayQuote->getName(),
                'surname'             => $inpostPayQuote->getSurname(),
                'masked_phone_number' => $inpostPayQuote->getMaskedPhoneNumber()
            ];

            return $this->confirmationInterfaceFactory->create(['data' => $data]);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $data = [
                'message' => __('Basket not found!')->render()
            ];
        }

        return $this->confirmationInterfaceFactory->create(['data' => $data]);
    }

    /**
     * @param string|null $status
     *
     * @return Phrase
     */
    private function getProperMessage(?string $status): Phrase
    {
        return match ($status) {
            default => __('Pending'),
            'SUCCESS' => __('Success'),
            'REJECT' => __('Reject')
        };
    }
}
