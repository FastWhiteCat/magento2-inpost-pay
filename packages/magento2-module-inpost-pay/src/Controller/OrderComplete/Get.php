<?php
declare(strict_types=1);

namespace InPost\InPostPay\Controller\OrderComplete;

use InPost\InPostPay\Model\ResourceModel\InPostPayOrder;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class Get implements HttpGetActionInterface
{
    private readonly RequestInterface $request;

    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly InPostPayOrder $inPostPayOrder,
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly LoggerInterface $logger
    ) {
        $this->request = $context->getRequest();
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $data = [];

        try {
            $basketId = $this->request->getParam('basketId');

            if ($basketId) {
                if ($this->inPostPayOrder->isOrderExistForBasketId($basketId)) {
                    $data = [
                        'action' => 'redirect',
                        'redirect' => $this->urlBuilder->getUrl('checkout/onepage/success/')
                    ];
                } elseif ($this->inPostPayQuote->isRefreshRequired($basketId)) {
                    $this->inPostPayQuote->updateRefreshRequired($basketId);
                    $data = ['action' => 'refresh'];
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $data = [
                'action' => 'refresh',
            ];
        }

        return $this->jsonFactory->create()->setData($data);
    }
}
