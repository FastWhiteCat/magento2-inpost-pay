<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Psr\Log\LoggerInterface;

class InPostBasketResolver
{
    public const ERROR_MESSAGE = 'error_message';
    public const ACTION = 'action';
    public const ACTION_RETRY = 'retry';
    public const ACTION_REJECT = 'reject';

    public function __construct(
        protected readonly GetCartForUser $cartForUser,
        protected readonly LoggerInterface $logger
    ) {
    }

    protected function getQuoteFromCartMaskIdAndContext(string $maskedCartId, ContextInterface $context): Quote
    {
        // @phpstan-ignore-next-line
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        // @phpstan-ignore-next-line
        $userId = (int)$context->getUserId();
        $quote = $this->cartForUser->execute($maskedCartId, $userId, $storeId);

        if (!$quote->getIsActive()) {
            throw new LocalizedException(__('Quote is inactive.'));
        }

        return $quote;
    }

    protected function extractCartMaskId(array $data): string
    {
        $maskedCartId = $data['cart_id'] ?? '';

        return is_scalar($maskedCartId) ? (string)$maskedCartId : '';
    }

    protected function prepareErrorResponse(?string $action = null, ?string $errorMessage = null): array
    {
        $errorResult = [];

        if ($action) {
            $errorResult[self::ACTION] = $action;
        }

        if ($errorMessage) {
            $errorResult[self::ERROR_MESSAGE] = $errorMessage;
        }

        return $errorResult;
    }
}
