<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Service\InitBasketProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Psr\Log\LoggerInterface;

class InPostPayInitBasketResolver implements ResolverInterface
{
    public const SUCCESS_RESULT_KEY = 'success';
    public const ERROR_RESULT_KEY = 'error';

    public function __construct(
        private readonly GetCartForUser $cartForUser,
        private readonly LoggerInterface $logger,
        private readonly InitBasketProcessor $initBasketProcessor
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
        $cartMaskId = $this->extractCartMaskId($args ?? []);

        try {
            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;
            $inPostPayQuote = $this->initBasketProcessor->process($quoteId);

            return [
                self::SUCCESS_RESULT_KEY => true,
                InPostPayQuoteInterface::BASKET_BINDING_API_KEY => $inPostPayQuote->getBasketBindingApiKey(),
                self::ERROR_RESULT_KEY => null
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['cart_mask_id' => $cartMaskId]);

            return [
                self::SUCCESS_RESULT_KEY => false,
                InPostPayQuoteInterface::BASKET_BINDING_API_KEY => '',
                self::ERROR_RESULT_KEY => $e->getMessage()
            ];
        }
    }

    private function getQuoteFromCartMaskIdAndContext(string $maskedCartId, ContextInterface $context): Quote
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

    private function extractCartMaskId(array $data): string
    {
        $maskedCartId = $data['cart_id'] ?? '';

        return is_scalar($maskedCartId) ? (string)$maskedCartId : '';
    }
}
