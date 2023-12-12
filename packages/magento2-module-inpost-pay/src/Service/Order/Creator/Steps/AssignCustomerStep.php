<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Order\Creator\Steps;

use InPost\InPostPay\Api\OrderProcessingStepInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class AssignCustomerStep extends OrderProcessingStep implements OrderProcessingStepInterface
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    public function process(Quote $quote, OrderInterface $inPostOrder): void
    {
        // @phpstan-ignore-next-line
        $quoteCustomerId = (int)$quote->getCustomer()->getId();
        $email = $inPostOrder->getAccountInfo()->getMail();
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        try {
            $customer = $this->customerRepository->get($email, $websiteId);
            $foundCustomerId = (int)$customer->getId();
            if ($foundCustomerId && $quoteCustomerId === $foundCustomerId) {
                $quote->assignCustomer($customer);
                $this->createLog(
                    sprintf(
                        'Customer account found by email: %s. Order will be assigned to customer ID: %s',
                        $email,
                        (int)$customer->getId()
                    )
                );
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->createLog(
                sprintf('Customer account not found by email: %s. Order will be processed for guest.', $email)
            );
        }
    }
}
