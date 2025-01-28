<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class AssignCustomerStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
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
                        $accountEmail,
                        (int)$customer->getId()
                    )
                );
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->createLog(
                sprintf('Customer account not found by email: %s. Order will be processed for guest.', $accountEmail)
            );
        }
    }
}
