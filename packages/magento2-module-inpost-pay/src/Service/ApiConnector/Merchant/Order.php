<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector\Merchant;

use InPost\InPostPay\Api\ApiConnector\Merchant\OrderInterface;
use InPost\InPostPay\Service\ApiConnector\Merchant\Dto\DtoOrderFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class Order implements OrderInterface
{
    public function __construct(
        private readonly RestRequest $restRequest,
        private readonly JsonSerializer $jsonSerializer,
        private readonly DtoOrderFactory $dtoOrderFactory,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return array
     */
    public function create(): array
    {
        $requestParams = $this->jsonSerializer->unserialize((string)$this->restRequest->getContent());
        $dtoOrder = $this->dtoOrderFactory->create($requestParams);
        $a = 2;

        return [
            'order' => 'OK'
        ];
    }
}
