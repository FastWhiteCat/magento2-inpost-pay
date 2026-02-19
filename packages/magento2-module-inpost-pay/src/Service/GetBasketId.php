<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Model\InPostPayQuoteFactory;
use InPost\InPostPay\Model\InPostPayQuoteRepository;

class GetBasketId
{
    private array $inPostPayQuote = [];

    public function __construct(
        private readonly GeneralConfigProvider $config,
        private readonly InPostPayQuoteFactory $inPostPayQuoteFactory,
        private readonly InPostPayQuoteRepository $inPostPayQuoteRepository,
        private readonly Random $randomDataGenerator,
    ) {
    }

    public function get(
        int $quoteId,
        bool $generateIfEmpty = false,
        ?string $gaClientId = null,
        ?string $fbclid = null,
        ?string $gclid = null
    ): ?string {
        if (!$this->config->isEnabled()) {
            return null;
        }

        if (!isset($this->inPostPayQuote[$quoteId])) {
            try {
                $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            } catch (LocalizedException $e) {
                $inPostPayQuote = null;
            }

            if ($generateIfEmpty && (!$inPostPayQuote || !$inPostPayQuote->getQuoteId())) {
                /** @var InPostPayQuoteInterface $inPostPayQuote */
                $inPostPayQuote = $this->inPostPayQuoteFactory->create();
                $inPostPayQuote->setQuoteId($quoteId);
                $inPostPayQuote->setBasketId($this->randomDataGenerator->getUniqueHash());
                $inPostPayQuote->setCartVersion(uniqid());
                $inPostPayQuote->setGaClientId($gaClientId);
                $inPostPayQuote->setFbclid($fbclid);
                $inPostPayQuote->setGclid($gclid);
                $this->inPostPayQuoteRepository->save($inPostPayQuote);
            }

            $this->inPostPayQuote[$quoteId] = $inPostPayQuote;
        }

        return isset($this->inPostPayQuote[$quoteId]) ? $this->inPostPayQuote[$quoteId]->getBasketId() : null;
    }
}
