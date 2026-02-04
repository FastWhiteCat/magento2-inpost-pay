<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayOrderRepositoryInterface;
use InPost\InPostPay\Exception\BasketNotFoundException;
use InPost\InPostPay\Provider\Config\SuccessPageUrlConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class InPostPayGetPlacedOrderDataResolver implements ResolverInterface
{
    public const SUCCESS_RESULT_KEY = 'success';
    public const ERROR_RESULT_KEY = 'error';
    public const ORDER_INCREMENT_ID_RESULT_KEY = 'increment_id';
    public const ORDER_POSTCODE_ID_RESULT_KEY = 'postcode';
    public const ORDER_EMAIL_ID_RESULT_KEY = 'email';
    public const ORDER_CART_ID_RESULT_KEY = 'cart';
    public const REDIRECT_RESULT_KEY = 'redirect';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly InPostPayOrderRepositoryInterface $inPostPayOrderRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CheckoutSession $checkoutSession,
        private readonly SuccessPageUrlConfigProvider $successPageUrlConfigProvider
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $basketBindingApiKey = $this->extractBasketBindingApiKey($args ?? []);

        try {
            $inPostPayOrder = $this->inPostPayOrderRepository->getByBasketBindingApiKey($basketBindingApiKey);
            $order = $this->orderRepository->get($inPostPayOrder->getOrderId());
            $this->setLastOrder($order);

            $billingAddress = $order->getBillingAddress();
            $postcode = (string)$billingAddress?->getPostcode();
            $email = $billingAddress ? (string)$billingAddress->getEmail() : (string)$order->getCustomerEmail();
            $cart = is_scalar($order->getQuoteId()) ? $this->cartRepository->get((int)$order->getQuoteId()) : null;

            return [
                self::SUCCESS_RESULT_KEY => true,
                self::ORDER_INCREMENT_ID_RESULT_KEY => $order->getIncrementId(),
                self::REDIRECT_RESULT_KEY => $this->successPageUrlConfigProvider->getOrderSuccessPageUrl($order),
                self::ORDER_EMAIL_ID_RESULT_KEY => $email,
                self::ORDER_POSTCODE_ID_RESULT_KEY => $postcode,
                self::ORDER_CART_ID_RESULT_KEY => [
                    'model' => $cart
                ],
                self::ERROR_RESULT_KEY => null
            ];
        } catch (BasketNotFoundException | LocalizedException $e) {
            $this->logger->error(
                $e->getMessage(),
                [InPostPayQuoteInterface::BASKET_BINDING_API_KEY => $basketBindingApiKey]
            );

            return [
                self::SUCCESS_RESULT_KEY => false,
                self::ORDER_INCREMENT_ID_RESULT_KEY => '',
                self::REDIRECT_RESULT_KEY => '',
                self::ERROR_RESULT_KEY => $e->getMessage()
            ];
        }
    }

    private function setLastOrder(OrderInterface $order): void
    {
        $this->checkoutSession->setLastQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastOrderId($order->getEntityId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
    }

    private function extractBasketBindingApiKey(array $data): string
    {
        $basketBindingApiKey = $data[InPostPayQuoteInterface::BASKET_BINDING_API_KEY] ?? '';

        return is_scalar($basketBindingApiKey) ? (string)$basketBindingApiKey : '';
    }
}
