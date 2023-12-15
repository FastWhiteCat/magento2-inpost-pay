<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\BrowserBinding;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\BrowserBinding;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class Delete implements HttpGetActionInterface
{
    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;
    private readonly ResponseInterface $response;

    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly SerializerInterface $serializer,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly BrowserBinding $browserBinding,
        private readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
    }

    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            $data = ['errorMessage' => __('Your session has expired')->render()];
            return $this->response->representJson($this->serializer->serialize($data));
        }

        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote->getId()) {
                $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId((int)$quote->getId());
                if ($inPostPayQuote->getQuoteId() && $inPostPayQuote->getBrowserId()) {
                    $response = $this->browserBinding->delete($inPostPayQuote->getBrowserId());
                    if (empty($response)) {
                        $inPostPayQuote->setBrowserId('');
                        $inPostPayQuote->setBrowserTrusted(false);
                        $this->inPostPayQuoteRepository->save($inPostPayQuote);
                    }
                    return $this->response->representJson($this->serializer->serialize($response));
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());

            throw $e;
        }

        return $this->response->representJson('');
    }
}
