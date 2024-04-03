<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\PayData;

use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Service\PayDataProcessor;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Get implements HttpPostActionInterface
{
    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly SerializerInterface $serializer,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly JsonFactory $jsonFactory,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly PayDataProcessor $payDataProcessor,
        private readonly QuoteManagement $quoteManagement,
        private readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
    }

    public function execute(): Json
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')->render()
            );
            $data = ['errorMessage' => __('Your session has expired')];

            return $this->jsonFactory->create()->setData($data);
        }

        $data = [];
        try {
            $quote = $this->getQuote();
            $this->quoteRestrictionsValidator->validate($quote, true);
            if ($quote->getId()) {
                $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
                $this->quoteRepository->getActive($quoteId);

                // @phpstan-ignore-next-line
                $params = $this->serializer->unserialize($this->request->getContent());

                if (isset($params['browser']) && isset($params['binding_place'])) {
                    $payData = $this->payDataProcessor->process($quoteId, $params, $this->request);

                    return $this->jsonFactory->create()->setData($payData);
                }
            }
        } catch (InPostPayRestrictedProductException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $this->messageManager->addWarningMessage(__('Connecting to InPost Pay failed.')->render());
            $data = [
                'errorMessage' => $e->getMessage(),
                'action' => 'reject'
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        return $this->jsonFactory->create()->setData($data);
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
