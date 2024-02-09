<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Psr\Log\LoggerInterface;

class InPostPayBasketConfirmationResolver implements ResolverInterface
{
    private const ACTION_RETRY = 'retry';
    private const ACTION_REJECT = 'reject';

    public function __construct(
        private readonly GetCartForUser $cartForUser,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly InPostPayQuote $inPostPayQuote,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $this->validateArgs($args);

        // @phpstan-ignore-next-line
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $maskedCartId = is_scalar($args && $args['cart_id']) ? (string)$args['cart_id'] : '';
        try {
            // @phpstan-ignore-next-line
            $quote = $this->cartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
            $this->quoteRestrictionsValidator->validate($quote, true);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return $this->prepareErrorResponse(self::ACTION_REJECT, $e->getMessage());
        }

        if (!$quote->getIsActive()) {
            return $this->prepareErrorResponse(self::ACTION_REJECT, 'Quote is inactive.');
        }

        $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;

        return $this->prepareBasketConfirmationData($quoteId);
    }

    private function prepareBasketConfirmationData(int $quoteId): array
    {
        try {
            if ($this->inPostPayQuote->isBasketConnected($quoteId)) {
                $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
                $basketConfirmationData = [
                    'status' => $inpostPayQuote->getStatus(),
                    'basket_id' => $inpostPayQuote->getBasketId(),
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
                    'masked_phone_number' => $inpostPayQuote->getMaskedPhoneNumber(),
                    'error_message' => null,
                    'action' => null
                ];
            } else {
                $basketConfirmationData = $this->prepareErrorResponse(self::ACTION_RETRY);
            }
        } catch (InPostPayRestrictedProductException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $basketConfirmationData = $this->prepareErrorResponse(self::ACTION_REJECT, $e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $basketConfirmationData = $this->prepareErrorResponse(self::ACTION_RETRY);
        }

        return $basketConfirmationData;
    }

    private function prepareErrorResponse(string $action, ?string $errorMessage = null): array
    {
        return [
            'status' => '',
            'basket_id' => '',
            'phone_number' => [
                'country_prefix' => '',
                'phone' => ''
            ],
            'browser' => [
                'browser_id' => '',
                'browser_trusted' => false,
            ],
            'name' => '',
            'surname' => '',
            'masked_phone_number' => '',
            'error_message' => $errorMessage,
            'action' => $action
        ];
    }

    private function validateArgs(array $args = null): void
    {
        if ($args === null || empty($args['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
    }
}
