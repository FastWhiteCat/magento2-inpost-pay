<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Service\PayDataProcessor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\App\Action\Context as AppActionContext;
use Psr\Log\LoggerInterface;

class InPostPayInitBasketResolver implements ResolverInterface
{
    public function __construct(
        private readonly AppActionContext $appActionContext,
        private readonly GetCartForUser $cartForUser,
        private readonly PayDataProcessor $payDataProcessor,
        private readonly LoggerInterface $logger
    ) {
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $this->validateArgs($args);

        // @phpstan-ignore-next-line
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $maskedCartId = is_scalar($args['input']['cart_id']) ? (string)$args['input']['cart_id'] : '';
        $quote = $this->cartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        if (!$quote->getIsActive()) {
            throw new GraphQlInputException(__('Quote is inactive.'));
        }

        $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;
        $inputParams = (array)$args['input'];

        try {
            // @phpstan-ignore-next-line
            $result = $this->payDataProcessor->process($quoteId, $inputParams, $this->appActionContext->getRequest());
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $result = [
                'error_message' => $e->getMessage(),
                'action' => 'reject'
            ];
        }

        return $result;
    }

    private function validateArgs(array $args = null): void
    {
        if ($args === null) {
            throw new GraphQlInputException(__('Empty input data.'));
        }

        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        if (empty($args['input']['binding_place'])) {
            throw new GraphQlInputException(__('Required parameter "binding_place" is missing'));
        }

        if (empty($args['input']['browser'])) {
            throw new GraphQlInputException(__('Required parameter "browser" is missing'));
        }
    }
}
