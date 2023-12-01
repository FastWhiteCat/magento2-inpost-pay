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

class GetConfirmation implements GetConfirmationInterface
{
    /**
     * @param CartRepositoryInterface           $cartRepository
     * @param InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
     * @param ConfirmationInterfaceFactory      $confirmationInterfaceFactory
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly ConfirmationInterfaceFactory $confirmationInterfaceFactory
    ) {
    }

    public function execute(int $cartId): ConfirmationInterface
    {
        try {
            $cart   = $this->cartRepository->get($cartId);
            $result = $this->inPostPayQuoteRepository->getByQuoteId((int)$cart->getId());

            $data = [
                'message'             => $this->getProperMessage($result->getStatus())->render(),
                'status'              => $result->getStatus() ?: '0',
                'browser_id'          => $result->getBrowserId(),
                'browser_trusted'     => $result->getBrowserTrusted(),
                'name'                => $result->getName(),
                'surname'             => $result->getSurname(),
                'masked_phone_number' => $result->getMaskedPhoneNumber()
            ];

            return $this->confirmationInterfaceFactory->create(['data' => $data]);
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
            'SUCCESS' => __('Success'),
            'REJECT' => __('Reject')
        };
    }
}
