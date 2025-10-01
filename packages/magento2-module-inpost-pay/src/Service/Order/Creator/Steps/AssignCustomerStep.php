<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Traits\AnonymizerTrait;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class AssignCustomerStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    use AnonymizerTrait;

    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly GeneralConfigProvider $generalConfigProvider,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        // @phpstan-ignore-next-line
        $quoteCustomerId = is_scalar($quote->getCustomer()->getId()) ? (int)$quote->getCustomer()->getId() : null;
        $accountEmail = $inPostOrder->getAccountInfo()->getMail();
        $websiteId = (int)$quote->getStore()->getWebsiteId();

        try {
            if ($quoteCustomerId) {
                $this->createLog(
                    sprintf(
                        'Basket has been initialized by logged-in user. Order will be assigned to customer ID: %s',
                        (int)$quote->getCustomerId()
                    )
                );
            } elseif ($this->generalConfigProvider->isAssigningGuestCartsToAccountByEmailEnabled($quote->getStoreId())
                && $accountEmail
            ) {
                $customer = $this->customerRepository->get($accountEmail, $websiteId);
                $quote->assignCustomer($customer);
                $quote->setCustomerIsGuest(false);
                $this->createLog(
                    sprintf(
                        'Customer account found by email: %s. Order will be assigned to customer ID: %s',
                        $this->anonymizeEmail($accountEmail),
                        (int)$customer->getId()
                    )
                );
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->createLog(
                sprintf(
                    'Customer account not found by email: %s. Order will be processed for guest.',
                    $this->anonymizeEmail($accountEmail),
                )
            );
        }

        $this->updateQuoteEmailWithInPostDeliveryInfo($quote, $inPostOrder->getDelivery()->getMail());
    }

    private function updateQuoteEmailWithInPostDeliveryInfo(
        Quote $quote,
        string $inPostDeliveryEmail
    ): void {
        if ($quote->getCustomerEmail() !== $inPostDeliveryEmail) {
            // Even if quote has been initialized for Logged-in user, quote customer email property will be updated
            // with delivery email chosen by customer in Mobile InPost Pay App on purpose so that the customer
            // will have his order assigned to an email he purposely selected in Mobile App.
            $quote->setCustomerEmail($inPostDeliveryEmail);
            $this->createLog(
                sprintf(
                    'Order email will be updated with delivery email from InPost Pay account: %s',
                    $inPostDeliveryEmail
                )
            );
        }
    }
}
