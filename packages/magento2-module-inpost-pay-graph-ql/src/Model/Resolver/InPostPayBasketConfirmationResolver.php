<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Exception\InPostPayRestrictedProductException;
use InPost\InPostPay\Model\ResourceModel\InPostPayQuote;
use InPost\InPostPay\Validator\QuoteRestrictionsValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Psr\Log\LoggerInterface;

class InPostPayBasketConfirmationResolver extends InPostBasketResolver implements ResolverInterface
{
    public function __construct(
        GetCartForUser $cartForUser,
        LoggerInterface $logger,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly QuoteRestrictionsValidator $quoteRestrictionsValidator,
        private readonly InPostPayQuote $inPostPayQuote
    ) {
        parent::__construct($cartForUser, $logger);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $cartMaskId = $this->extractCartMaskId($args ?? []);
        try {
            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $this->quoteRestrictionsValidator->validate($quote, true);

            return $this->prepareBasketConfirmationData((is_scalar($quote->getId())) ? (int)$quote->getId() : 0);
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['cart_mask_id' => $cartMaskId]);

            return $this->prepareErrorResponse(self::ACTION_REJECT, $e->getMessage());
        }
    }

    private function prepareBasketConfirmationData(int $quoteId): array
    {
        try {
            if ($this->inPostPayQuote->isBasketConnected($quoteId)) {
                $inpostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
                $result = [
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
                    'masked_phone_number' => $inpostPayQuote->getMaskedPhoneNumber()
                ];
            } else {
                $result = $this->prepareErrorResponse(self::ACTION_RETRY);
            }
        } catch (InPostPayRestrictedProductException $e) {
            $this->logger->error($e->getMessage(), ['quote_id' => $quoteId]);
            $result = $this->prepareErrorResponse(self::ACTION_REJECT, $e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['quote_id' => $quoteId]);
            $result = $this->prepareErrorResponse(self::ACTION_RETRY);
        }

        return $result;
    }
}
