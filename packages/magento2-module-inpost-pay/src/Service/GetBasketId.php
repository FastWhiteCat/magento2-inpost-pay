<?php
declare(strict_types=1);

namespace InPost\InPostPay\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Model\InPostPayQuoteFactory;
use InPost\InPostPay\Model\InPostPayQuoteRepository;
class GetBasketId
{
    private $inPostPayQuote = [];

    public function __construct(
        private readonly GeneralConfigProvider $config,
        private readonly InPostPayQuoteFactory $inPostPayQuoteFactory,
        private readonly InPostPayQuoteRepository $inPostPayQuoteRepository,
        private readonly Random $randomDataGenerator,
    ) {
    }

    public function get(int $quoteId, $generateIfEmpty = false): ?string
    {
        if (!$this->config->isEnabled()) {
            return '';
        }

        if (!isset($this->inPostPayQuote[$quoteId])) {
            try {
                $inPostPayQuote = $this->inPostPayQuoteRepository->getByQuoteId($quoteId);
            } catch (LocalizedException $e) {
                $inPostPayQuote = null;
            }

            if ($generateIfEmpty && (!$inPostPayQuote || !$inPostPayQuote->getQuoteId())) {
                $inPostPayQuote = $this->inPostPayQuoteFactory->create();
                $inPostPayQuote->setQuoteId($quoteId);
                $inPostPayQuote->setBasketId($this->randomDataGenerator->getUniqueHash());
                $this->inPostPayQuoteRepository->save($inPostPayQuote);
            }

            $this->inPostPayQuote[$quoteId] = $inPostPayQuote;
        }

        return isset($this->inPostPayQuote[$quoteId]) ? $this->inPostPayQuote[$quoteId]->getBasketId() : null;
    }
}
