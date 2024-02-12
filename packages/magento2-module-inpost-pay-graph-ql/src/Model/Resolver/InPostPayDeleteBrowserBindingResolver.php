<?php

declare(strict_types=1);

namespace InPost\InPostPayGraphQl\Model\Resolver;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use InPost\InPostPay\Service\ApiConnector\BrowserBinding;
use Psr\Log\LoggerInterface;

class InPostPayDeleteBrowserBindingResolver extends InPostBasketResolver implements ResolverInterface
{
    private const SUCCESS = 'success';

    public function __construct(
        GetCartForUser $cartForUser,
        LoggerInterface $logger,
        private readonly BrowserBinding $browserBinding,
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository
    ) {
        parent::__construct($cartForUser, $logger);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): array
    {
        $result = [self::SUCCESS => false];
        $cartMaskId = $this->extractCartMaskId($args ?? []);

        try {
            $quote = $this->getQuoteFromCartMaskIdAndContext($cartMaskId, $context);
            $quoteId = (is_scalar($quote->getId())) ? (int)$quote->getId() : 0;
            $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            if ($inPostPayQuote->getQuoteId() && $inPostPayQuote->getBrowserId()) {
                $this->deleteBrowserBinding($inPostPayQuote);
                $result[self::SUCCESS] = true;
            } else {
                $errorPhrase = __('Basked does not have browser ID so binding cannot be proceed.');
                $this->logger->error($errorPhrase->getText(), ['cart_mask_id' => $cartMaskId]);
                $result = $this->prepareErrorResponse(null, $errorPhrase->render());
            }

            return $result;
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage(), ['cart_mask_id' => $cartMaskId]);

            return $this->prepareErrorResponse(
                null,
                sprintf(
                    '%s %s',
                    __('There was an error during basket browser binding delete process.')->render(),
                    __('Try repeating this action or contact Merchant Administrator.')->render()
                )
            );
        }
    }

    /**
     * @param InPostPayQuoteInterface $inPostPayQuote
     * @return void
     * @throws LocalizedException
     * @throws CouldNotSaveException
     */
    private function deleteBrowserBinding(InPostPayQuoteInterface $inPostPayQuote): void
    {
        $response = $this->browserBinding->delete((string)$inPostPayQuote->getBrowserId());
        if (empty($response)) {
            $inPostPayQuote->setBrowserId('');
            $inPostPayQuote->setBrowserTrusted(false);
            $this->inPostPayQuoteRepository->save($inPostPayQuote);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function prepareErrorResponse(?string $action = null, ?string $errorMessage = null): array
    {
        $errorResult = [
            self::SUCCESS => false
        ];

        if ($errorMessage) {
            $errorResult[self::ERROR_MESSAGE] = $errorMessage;
        }

        return $errorResult;
    }
}
