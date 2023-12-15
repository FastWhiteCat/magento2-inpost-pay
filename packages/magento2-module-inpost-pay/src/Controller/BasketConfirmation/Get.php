<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\BasketConfirmation;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Enum\InPostBasketStatus;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class Get implements HttpGetActionInterface
{
    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;

    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly JsonFactory $jsonFactory,
        private readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')->render()
            );
            $data = ['errorMessage' => __('Your session has expired')->render()];

            return $this->jsonFactory->create()->setData($data);
        }
        $data = [];

        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote->getId()) {
                $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
                $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

                $data = [
                    'status' => $inpostPayQuote->getStatus(),
                    'message' => $this->getProperMessage($inpostPayQuote->getStatus())->render(),
                    'phone_number' => [
                        'country_prefix' => (string)$inpostPayQuote->getCountryPrefix(),
                        'phone' => (string)$inpostPayQuote->getPhone()
                    ],
                    'browser' => [
                        'browser_id' => $inpostPayQuote->getBrowserId(),
                        'browser_trusted' => $inpostPayQuote->getBrowserTrusted(),
                    ],
                    'name' => $inpostPayQuote->getName(),
                    'surname' => $inpostPayQuote->getSurname(),
                    'masked_phone_number' => $inpostPayQuote->getMaskedPhoneNumber()
                ];
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $data = [
                'action' => 'retry'
            ];
        }

        return $this->jsonFactory->create()->setData($data);
    }

    /**
     * @param string $status
     *
     * @return Phrase
     */
    private function getProperMessage(string $status): Phrase
    {
        return match ($status) {
            default => __('Pending'),
            InPostBasketStatus::SUCCESS->value => __('Success'),
            InPostBasketStatus::REJECT->value => __('Reject')
        };
    }
}
