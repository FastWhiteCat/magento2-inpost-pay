<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\DataTransfer\OrderToInPostOrder;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\Merchant\OrderInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
use InPost\InPostPay\Service\DataTransfer\OrderToInPostOrder\OrderToInPostOrderOrderDetailsDataTransfer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Order\OrderDetails\AdditionalOrderParametersInterface;

class AddAdditionalAnalyticsOrderParamsToOrderDetailsPlugin
{
    /**
     * @param AnalyticsConfigProvider $analyticsConfigProvider
     * @param InPostPayOrderRepositoryInterface $inPostPayOrderRepository
     * @param AdditionalOrderParametersInterfaceFactory $additionalOrderParametersInterfaceFactory
     */
    public function __construct(
        private readonly AnalyticsConfigProvider $analyticsConfigProvider,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly AdditionalOrderParametersInterfaceFactory $additionalOrderParametersInterfaceFactory
    ) {
    }

    /**
     * @param OrderToInPostOrderOrderDetailsDataTransfer $subject
     * @param $result
     * @param Order $order
     * @param OrderInterface $inPostOrder
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterTransfer( //@phpstan-ignore-line
        OrderToInPostOrderOrderDetailsDataTransfer $subject,
        $result,
        Order $order,
        OrderInterface $inPostOrder
    ): void {
        $storeId = is_scalar($order->getStoreId()) ? (int) $order->getStoreId() : 0;

        if (!$this->analyticsConfigProvider->isAnalyticsEnabled($storeId)) {
            return;
        }

        try {
            $orderId = is_scalar($order->getId()) ? (int) $order->getId() : 0;
            $inPostPayOrder = $this->inPostPayOrderRepository->getByOrderId($orderId, true);
            $existingAdditionalOrderParameters = $inPostOrder->getOrderDetails()->getOrderAdditionalParameters();
            $analyticsOrderParameters = $this->prepareAdditionalAnalyticsOrderParameters($inPostPayOrder, $storeId);
            $newAdditionalOrderParameters = array_merge($existingAdditionalOrderParameters, $analyticsOrderParameters);
            $inPostOrder->getOrderDetails()->setOrderAdditionalParameters($newAdditionalOrderParameters);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * @param InPostPayOrderInterface $inPostPayOrder
     * @param int $storeId
     * @return array
     */
    private function prepareAdditionalAnalyticsOrderParameters(
        InPostPayOrderInterface $inPostPayOrder,
        int $storeId
    ): array {
        $additionalOrderParameters = [];
        $clientId = $inPostPayOrder->getGaClientId();
        $fbclid = $inPostPayOrder->getFbclid();
        $gclid = $inPostPayOrder->getGclid();

        if ($clientId) {
            /** @var AdditionalOrderParametersInterface $clientIdAdditionalOrderParameter */
            $clientIdAdditionalOrderParameter = $this->additionalOrderParametersInterfaceFactory->create();
            $clientIdAdditionalOrderParameter->setKey(InPostPayOrderInterface::CLIENT_ID);
            $clientIdAdditionalOrderParameter->setValue($clientId);
            $additionalOrderParameters[] = $clientIdAdditionalOrderParameter;
        }

        if ($fbclid && $this->analyticsConfigProvider->isSendingFbclidEnabled($storeId)) {
            /** @var AdditionalOrderParametersInterface $clientIdAdditionalOrderParameter */
            $fbclidAdditionalOrderParameter = $this->additionalOrderParametersInterfaceFactory->create();
            $fbclidAdditionalOrderParameter->setKey(InPostPayOrderInterface::FBCLID);
            $fbclidAdditionalOrderParameter->setValue($fbclid);
            $additionalOrderParameters[] = $fbclidAdditionalOrderParameter;
        }

        if ($gclid && $this->analyticsConfigProvider->isSendingGclidEnabled($storeId)) {
            /** @var AdditionalOrderParametersInterface $clientIdAdditionalOrderParameter */
            $gclidAdditionalOrderParameter = $this->additionalOrderParametersInterfaceFactory->create();
            $gclidAdditionalOrderParameter->setKey(InPostPayOrderInterface::GCLID);
            $gclidAdditionalOrderParameter->setValue($gclid);
            $additionalOrderParameters[] = $gclidAdditionalOrderParameter;
        }

        return $additionalOrderParameters;
    }
}
