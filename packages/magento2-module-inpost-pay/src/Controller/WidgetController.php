<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class WidgetController
{
    public const SUCCESS_RESULT_KEY = 'success';
    public const ERROR_RESULT_KEY = 'error';

    protected readonly ManagerInterface $messageManager;
    protected readonly RequestInterface $request;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Validator $formKeyValidator
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly Context $context,
        protected readonly CheckoutSession $checkoutSession,
        protected readonly Validator $formKeyValidator,
        protected readonly JsonFactory $jsonFactory,
        protected readonly LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
    }

    /**
     * @return JsonResult|null
     */
    protected function getFailedFormKeyValidationResult(): ?JsonResult
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')->render()
            );
            $data = ['errorMessage' => __('Your session has expired')->render()];

            return $this->jsonFactory->create()->setData($data);
        }

        return null;
    }
}
