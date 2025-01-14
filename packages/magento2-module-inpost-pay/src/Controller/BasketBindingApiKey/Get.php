<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\BasketBindingApiKey;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Controller\WidgetController;
use InPost\InPostPay\Service\InitBasketProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Get extends WidgetController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Validator $formKeyValidator
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param InitBasketProcessor $initBasketProcessor
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Validator $formKeyValidator,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly InitBasketProcessor $initBasketProcessor,
        private readonly QuoteManagement $quoteManagement
    ) {
        parent::__construct($context, $checkoutSession, $formKeyValidator, $jsonFactory, $logger);
    }

    public function execute(): Json
    {
        if ($failedFormKeyValidationResult = $this->getFailedFormKeyValidationResult()) {
            return $failedFormKeyValidationResult;
        }

        $result = [];
        try {
            $quote = $this->getQuote();
            if ($quote->getId()) {
                $quoteId = (int)$quote->getId();
                $this->quoteRepository->getActive($quoteId);
                $inPostPayQuote = $this->initBasketProcessor->process($quoteId);

                $result = [
                    self::SUCCESS_RESULT_KEY  => true,
                    InPostPayQuoteInterface::BASKET_BINDING_API_KEY => $inPostPayQuote->getBasketBindingApiKey()
                ];
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addWarningMessage(__('Connecting to InPost Pay failed.')->render());
            $this->logger->error($e->getMessage(), $e->getTrace());

            $result = [
                self::SUCCESS_RESULT_KEY  => false,
                self::ERROR_RESULT_KEY  => $e->getMessage()
            ];
        }

        return $this->jsonFactory->create()->setData($result);
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function getQuote(): CartInterface
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote->getId()) {
            $quoteId = $this->quoteManagement->createEmptyCart();
            $quote = $this->quoteRepository->get($quoteId);
            // @phpstan-ignore-next-line
            $this->checkoutSession->replaceQuote($quote);
        }

        return $quote;
    }
}
