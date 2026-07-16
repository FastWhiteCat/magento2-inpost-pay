<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Provider\Config\AnalyticsConfigProvider;
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

    private const ANALYTICS_PARAM_MAX_LENGTH = 255;

    public function __construct(
        private readonly GetCartForUser $cartForUser,
        private readonly LoggerInterface $logger,
        private readonly InitBasketProcessor $initBasketProcessor,
        private readonly AnalyticsConfigProvider $analyticsConfigProvider
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
        $args = $args ?? [];
        $cartMaskId = $this->extractCartMaskId($args);

        try {
            $gaClientId = null;
            $fbclid = null;
            $gclid = null;
            $ttclid = null;

            if ($this->analyticsConfigProvider->isAnalyticsEnabled()) {
                $gaClientId = $this->getAnalyticsParam($args, InPostPayQuoteInterface::GA_CLIENT_ID);
                $fbclid = $this->getAnalyticsParam($args, InPostPayQuoteInterface::FBCLID);
                $gclid = $this->getAnalyticsParam($args, InPostPayQuoteInterface::GCLID);
                $ttclid = $this->getAnalyticsParam($args, InPostPayQuoteInterface::TTCLID);
            }

            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;
            $inPostPayQuote = $this->initBasketProcessor->process(
                $quoteId,
                $gaClientId,
                $fbclid,
                $gclid,
                $ttclid
            );

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

    /**
     * @param array $args
     * @param string $analyticsParamKey
     * @return string|null
     * @throws LocalizedException
     */
    private function getAnalyticsParam(array $args, string $analyticsParamKey): ?string
    {
        $value = $args[$analyticsParamKey] ?? null;
        $value = is_scalar($value) ? (string)$value : null;

        if ($value !== null) {
            $this->validateAnalyticsParam($analyticsParamKey, $value);
        }

        return $value;
    }

    /**
     * @param string $analyticsParamKey
     * @param string $analyticsParamValue
     * @return void
     * @throws LocalizedException
     */
    private function validateAnalyticsParam(string $analyticsParamKey, string $analyticsParamValue): void
    {
        if (strlen($analyticsParamValue) > self::ANALYTICS_PARAM_MAX_LENGTH) {
            throw new LocalizedException(__('Analytics param %1 is too long.', $analyticsParamKey));
        }

        if (preg_match('/^[A-Za-z0-9._-]+$/', $analyticsParamValue) !== 1) {
            throw new LocalizedException(__('Analytics param %1 is not valid.', $analyticsParamKey));
        }
    }
}
