<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Service\PayDataProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\App\Action\Context as AppActionContext;
use Psr\Log\LoggerInterface;

class InPostPayInitBasketResolver extends InPostBasketResolver implements ResolverInterface
{
    public function __construct(
        GetCartForUser $cartForUser,
        LoggerInterface $logger,
        private readonly AppActionContext $appActionContext,
        private readonly PayDataProcessor $payDataProcessor
    ) {
        parent::__construct($cartForUser, $logger);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $inputData = $args && isset($args['input']) ? (array)$args['input'] : [];
        $cartMaskId = $this->extractCartMaskId($inputData);

        try {
            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;

            // @phpstan-ignore-next-line
            return $this->payDataProcessor->process($quoteId, $inputData, $this->appActionContext->getRequest());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['cart_mask_id' => $cartMaskId]);

            return $this->prepareErrorResponse(self::ACTION_REJECT, $e->getMessage());
        }
    }
}
