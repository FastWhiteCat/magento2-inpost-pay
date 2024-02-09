<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\MobileLink;

use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use InPost\InPostPay\Service\ApiConnector\BasketBindingCheck;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class Get implements HttpGetActionInterface
{
    public const MOBILE_LINK = 'inpost://izilink?basket_id=';
    public const SANDBOX_MOBILE_LINK = 'inpost://izilinksandbox?basket_id=';
    private readonly ManagerInterface $messageManager;
    private readonly RequestInterface $request;

    public function __construct(
        Context $context,
        private readonly BasketBindingCheck $basketBindingCheck,
        private readonly SandboxConfigProvider $sandboxConfigProvider,
        private readonly CheckoutSession $checkoutSession,
        private readonly Validator $formKeyValidator,
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
            $data = ['errorMessage' => __('Your session has expired')];

            return $this->jsonFactory->create()->setData($data);
        }

        $data = [];
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote->getId()) {
                $quoteId = is_scalar($quote->getId()) ? (int)$quote->getId() : 0;
                $result = $this->basketBindingCheck->execute($quoteId);

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

        return $this->jsonFactory->create()->setData($data);
    }
}
