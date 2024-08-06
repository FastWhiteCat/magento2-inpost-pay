<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\Data\InPostPayOrderInterface;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\BasketNotFoundException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote as InPostPayQuoteResource;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InPostPayGetPlacedOrderDataResolver extends InPostBasketResolver implements ResolverInterface
{
    private const ORDER_ID = 'order_id';
    private const STATUS = 'status';
    private const STATUS_LABEL = 'status_label';
    private const CART_VERSION = 'cart_version';

    public function __construct(
        GetCartForUser $cartForUser,
        LoggerInterface $logger,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly InPostPayQuoteResource $inPostPayQuoteResource,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CheckoutSession $checkoutSession
    ) {
        parent::__construct($cartForUser, $logger);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $basketId = $this->extractBasketId($args ?? []);
        $cartVersion = '';

        try {
            $inPostPayData = $this->inPostPayQuoteResource->getCartVersionAndOrderId($basketId);

            if (empty($inPostPayData)) {
                $inPostPayData = $this->getInPostPayOrderDataByBasketId($basketId);
            }

            if (empty($inPostPayData)) {
                throw new BasketNotFoundException(__('Could not find a basket with ID:%1', $basketId));
            }

            $cartVersion = (string)($inPostPayData[InPostPayQuoteInterface::CART_VERSION] ?? '');
            $orderId = (int)($inPostPayData[InPostPayOrderInterface::ORDER_ID] ?? 0);

            if ($orderId) {
                $order = $this->orderRepository->get($orderId);
                $this->setLastOrder($order);
                $result = $this->preparePlacedOrderResponse($order, $cartVersion);
            } else {
                $result = $this->prepareUnplacedOrderResponse($cartVersion);
            }

            return $result;
        } catch (BasketNotFoundException $e) {
            $this->logger->error($e->getMessage(), ['basket_id' => $basketId]);

            $errorResponse = $this->prepareErrorResponse(
                InPostBasketResolver::ACTION_REJECT,
                __($e->getMessage())->render()
            );

            $errorResponse[self::CART_VERSION] = $cartVersion;

            return $errorResponse;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['basket_id' => $basketId]);

            $errorResponse = $this->prepareErrorResponse(
                InPostBasketResolver::ACTION_REJECT,
                __('There was an error while checking if order was placed in InPost Pay Mobile App.')->render()
            );

            $errorResponse[self::CART_VERSION] = $cartVersion;

            return $errorResponse;
        }
    }

    private function preparePlacedOrderResponse(OrderInterface $order, string $cartVersion): array
    {
        // @phpstan-ignore-next-line
        $statusLabel = (string)$order->getStatusLabel();
        return [
            self::CART_VERSION => $cartVersion,
            self::ACTION => self::ACTION_REDIRECT,
            self::ORDER_ID => (string)$order->getIncrementId(),
            self::STATUS => (string)$order->getStatus(),
            self::STATUS_LABEL => $statusLabel
        ];
    }

    private function prepareUnplacedOrderResponse(string $cartVersion): array
    {
        return [
            self::CART_VERSION => $cartVersion,
            self::ACTION => self::ACTION_REFRESH
        ];
    }

    private function setLastOrder(OrderInterface $order): void
    {
        $this->checkoutSession->setLastQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastOrderId($order->getEntityId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
    }

    private function extractBasketId(array $data): string
    {
        $basketId = $data['basket_id'] ?? '';

        return is_scalar($basketId) ? (string)$basketId : '';
    }

    private function getInPostPayOrderDataByBasketId(string $basketId): array
    {
        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByBasketId($basketId);
            $inPostPayData[InPostPayOrderInterface::ORDER_ID] = $inPostPayOrder->getOrderId();
        } catch (NoSuchEntityException | LocalizedException $e) {
            $inPostPayData = [];
        }

        return $inPostPayData;
    }
}
