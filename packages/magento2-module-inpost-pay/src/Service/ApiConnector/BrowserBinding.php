<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\ApiConnector;

use Exception;
use InPost\InPostPay\Api\ApiConnector\ConnectorInterface;
use InPost\InPostPay\Model\IziApi\Request\DeleteBrowserBindingRequest;
use InPost\InPostPay\Model\IziApi\Request\DeleteBrowserBindingRequestFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class BrowserBinding
{
    public function __construct(
        private readonly ConnectorInterface $connector,
        private readonly DeleteBrowserBindingRequestFactory $deleteBrowserBindingRequestFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param string $browserId
     *
     * @return array
     * @throws LocalizedException
     */
    public function delete(string $browserId): array
    {
        /** @var DeleteBrowserBindingRequest $request */
        $request = $this->deleteBrowserBindingRequestFactory->create();

        $request->setParams([
            'browser_id' => $browserId,
        ]);

        try {
            return $this->connector->sendRequest($request);
        } catch (Exception $e) {
            $errorMsg = __('There was a problem with binding checking. Details: %1', $e->getMessage());
            $this->logger->critical($errorMsg->render());

            throw new LocalizedException($errorMsg);
        }
    }
}
