<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\BasketBindingApiKey;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Controller\WidgetController;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Service\InitBasketProcessor;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
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
class Get extends WidgetController implements HttpPostActionInterface
{
    private const ANALYTICS_PARAM_MAX_LENGTH = 255;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Validator $formKeyValidator
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     * @param CartRepositoryInterface $quoteRepository
     * @param InitBasketProcessor $initBasketProcessor
     * @param QuoteManagement $quoteManagement
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Validator $formKeyValidator,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly InitBasketProcessor $initBasketProcessor,
        private readonly QuoteManagement $quoteManagement,
        private readonly AnalyticsConfigProvider $analyticsConfigProvider
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
            $gaClientId = null;
            $fbclid = null;
            $gclid = null;

            if ($this->analyticsConfigProvider->isAnalyticsEnabled()) {
                $gaClientId = $this->getGaClientIdFromRequestParams();
                $fbclid = $this->getFbclidFromRequestParams();
                $gclid = $this->getGclidFromRequestParams();
            }

            $quote = $this->getQuote();
            if ($quote->getId()) {
                $quoteId = (int)$quote->getId();
                $this->quoteRepository->getActive($quoteId);
                $inPostPayQuote = $this->initBasketProcessor->process(
                    $quoteId,
                    $gaClientId,
                    $fbclid,
                    $gclid
                );

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

    /**
     * @return string|null
     * @throws LocalizedException
     */
    private function getGaClientIdFromRequestParams(): ?string
    {
        $gaClientId = $this->request->getParam(InPostPayQuoteInterface::GA_CLIENT_ID);
        $gaClientId = is_scalar($gaClientId) ? (string)$gaClientId : null;

        if ($gaClientId !== null) {
            $this->validateAnalyticsParam(InPostPayQuoteInterface::GA_CLIENT_ID, $gaClientId);
        }

        return $gaClientId;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    private function getFbclidFromRequestParams(): ?string
    {
        $fbclid = $this->request->getParam(InPostPayQuoteInterface::FBCLID);
        $fbclid = is_scalar($fbclid) ? (string)$fbclid : null;

        if ($fbclid !== null) {
            $this->validateAnalyticsParam(InPostPayQuoteInterface::FBCLID, $fbclid);
        }

        return $fbclid;
    }

    /**
     * @return string|null
     * @throws LocalizedException
     */
    private function getGclidFromRequestParams(): ?string
    {
        $gclid = $this->request->getParam(InPostPayQuoteInterface::GCLID);
        $gclid = is_scalar($gclid) ? (string)$gclid : null;

        if ($gclid !== null) {
            $this->validateAnalyticsParam(InPostPayQuoteInterface::GCLID, $gclid);
        }

        return $gclid;
    }

    /**
     * @param string $analyticsParamKey
     * @param string $analyticsParamValue
     * @return void
     * @throws LocalizedException
     */
    private function validateAnalyticsParam(string $analyticsParamKey, string $analyticsParamValue): void
    {
        if (strlen($analyticsParamValue) > self::ANALYTICS_PARAM_MAX_LENGTH) {
            throw new LocalizedException(__('Analytics param %1 is too long.', $analyticsParamKey));
        }

        if (preg_match('/^[A-Za-z0-9._-]+$/', $analyticsParamValue) !== 1) {
            throw new LocalizedException(__('Analytics param %1 is not valid.', $analyticsParamKey));
        }
    }
}
