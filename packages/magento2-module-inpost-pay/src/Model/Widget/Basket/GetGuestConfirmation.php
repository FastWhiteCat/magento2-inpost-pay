<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Widget\Basket;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterface;
use InPost\InPostPay\Api\Widget\Basket\ConfirmationInterfaceFactory;
use InPost\InPostPay\Api\Widget\Basket\GetGuestConfirmationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GetGuestConfirmation implements GetGuestConfirmationInterface
{
    /**
     * @param QuoteIdMaskFactory                $quoteIdMaskFactory
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param ConfirmationInterfaceFactory      $confirmationInterfaceFactory
     */
    public function __construct(
        private readonly QuoteIdMaskFactory $quoteIdMaskFactory,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly ConfirmationInterfaceFactory $confirmationInterfaceFactory
    ) {
    }

    public function execute(string $cartId): ConfirmationInterface
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        try {
            $result = $this->inPostPayQuoteRepository->getByQuoteId((int)$quoteIdMask->getQuoteId());

            $data = [
                'message'             => $this->getProperMessage($result->getStatus())->render(),
                'status'              => $result->getStatus() ?: '0',
                'browser_id'          => $result->getBrowserId(),
                'browser_trusted'     => $result->getBrowserTrusted(),
                'name'                => $result->getName(),
                'surname'             => $result->getSurname(),
                'masked_phone_number' => $result->getMaskedPhoneNumber()
            ];
        } catch (LocalizedException $e) {
            $data = [
                'message' => __('Basket not found!')->render()
            ];
        }

        return $this->confirmationInterfaceFactory->create(['data' => $data]);

    }

    /**
     * @param string|null $getStatus
     *
     * @return Phrase
     */
    private function getProperMessage(?string $getStatus): Phrase
    {
        return match ($getStatus) {
            null => __('Pending'),
            'success' => __('Success'),
            'reject' => __('Reject')
        };
    }

}
