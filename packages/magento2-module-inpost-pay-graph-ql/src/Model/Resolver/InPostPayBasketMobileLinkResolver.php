<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Controller\MobileLink\Get;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use InPost\InPostPay\Service\ApiConnector\BasketBindingCheck;
use InPost\InPostPay\Provider\Config\SandboxConfigProvider;
use Psr\Log\LoggerInterface;

class InPostPayBasketMobileLinkResolver extends InPostBasketResolver implements ResolverInterface
{
    public function __construct(
        GetCartForUser $cartForUser,
        LoggerInterface $logger,
        private readonly BasketBindingCheck $basketBindingCheck,
        private readonly SandboxConfigProvider $sandboxConfigProvider
    ) {
        parent::__construct($cartForUser, $logger);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        try {
            $cartMaskId = $this->extractCartMaskId($args);
            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $result = $this->basketBindingCheck->execute((is_scalar($quote->getId())) ? (int)$quote->getId() : 0);

            return [
                'link' => sprintf(
                    '%s%s',
                    $this->sandboxConfigProvider->isSandboxEnabled() ? Get::SANDBOX_MOBILE_LINK : Get::MOBILE_LINK,
                    $result['inpost_basket_id'] ?? ''
                )
            ];
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return $this->prepareErrorResponse(null, $e->getMessage());
        }
    }
}
