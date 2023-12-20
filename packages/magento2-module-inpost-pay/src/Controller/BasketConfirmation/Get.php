<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\BasketConfirmation;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use Magento\Checkout\Model\Session as CheckoutSession;
use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use InPost\InPostPay\Service\GetBasketId;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
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
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly CreateOrUpdateBasket $createOrUpdateBasket,
        private readonly CookieManagerInterface $cookieManager,
        private readonly GetBasketId $getBasketId,
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

                if ($this->inPostPayQuote->isBasketConnected($quoteId)) {
                    $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);

                    $data = [
                        'status' => $inpostPayQuote->getStatus(),
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
                } elseif ($browserId = $this->cookieManager->getCookie('BrowserId')) {
                    $basketId = $this->getBasketId->get($quoteId, true);
                    if ($basketId) {
                        $this->createOrUpdateBasket->execute($quote, $browserId, $basketId);
                    }
                } else {
                    $data = [
                        'action' => 'retry'
                    ];
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $data = [
                'action' => 'retry'
            ];
        }

        return $this->jsonFactory->create()->setData($data);
    }
}
