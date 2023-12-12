<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\MobileLink;

use InPost\InPostPay\Api\Widget\Basket\MobileLinkInterfaceFactory;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Service\ApiConnector\BindingBasket;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class Get implements HttpGetActionInterface
{
    private const MOBILE_LINK = 'inpost://izilink?basket_id=';
    private const SANDBOX_MOBILE_LINK = 'inpost://izilinksandbox?basket_id=';
    private readonly ManagerInterface $messageManager;
    private readonly RedirectFactory $resultRedirectFactory;
    private readonly RequestInterface $request;
    private readonly ResponseInterface $response;

    public function __construct(
        Context $context,
        private readonly BindingBasket $bindingBasket,
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
    }

    public function execute(): ResponseInterface
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            $data = ['errorMessage' => __('Your session has expired')];

            return $this->response->representJson($this->serializer->serialize($data));
        }

        $data = [];
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote->getId()) {
                $result = $this->bindingBasket->checkBinding((int)$quote->getId());

                if (isset($result['inpost_basket_id'])) {
                    if ($this->sandboxConfigProvider->isSandboxEnabled()) {
                        $link = self::SANDBOX_MOBILE_LINK . $result['inpost_basket_id'];
                    } else {
                        $link = self::MOBILE_LINK . $result['inpost_basket_id'];
                    }

                    $data = ['link' => $link];
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }

        return $this->response->representJson($this->serializer->serialize($data));
    }
}
