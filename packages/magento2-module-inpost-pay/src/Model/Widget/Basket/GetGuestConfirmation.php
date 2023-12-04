<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterfaceFactory;
use InPost\InPostPay\Api\Widget\Basket\GetGuestConfirmationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Monolog\Logger;

class GetGuestConfirmation implements GetGuestConfirmationInterface
{
    /**
     * @param MaskedQuoteIdToQuoteIdInterface   $maskedQuoteIdToQuoteId
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param ConfirmationInterfaceFactory      $confirmationInterfaceFactory
     * @param Logger                            $logger
     */
    public function __construct(
        private readonly MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly ConfirmationInterfaceFactory $confirmationInterfaceFactory,
        private readonly Logger $logger
    ) {
    }

    public function execute(string $cartId): ConfirmationInterface
    {
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
            $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

            $data = [
                'message'             => $this->getProperMessage($inpostPayQuote->getStatus())->render(),
                'status'              => $inpostPayQuote->getStatus() ?: '0',
                'browser_id'          => $inpostPayQuote->getBrowserId(),
                'browser_trusted'     => $inpostPayQuote->getBrowserTrusted(),
                'name'                => $inpostPayQuote->getName(),
                'surname'             => $inpostPayQuote->getSurname(),
                'masked_phone_number' => $inpostPayQuote->getMaskedPhoneNumber()
            ];
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
            'SUCCESS' => __('Success'),
            'REJECT' => __('Reject'),
            default => __('Pending')
        };
    }
}
