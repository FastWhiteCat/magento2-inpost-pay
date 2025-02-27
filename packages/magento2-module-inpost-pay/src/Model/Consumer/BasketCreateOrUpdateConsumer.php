<?php

declare(strict_types=1);

namespace InPost\InPostPay\Model\Consumer;

use Exception;
use InPost\InPostPay\Api\Data\InPostPayQuoteInterface;
use InPost\InPostPay\Api\InPostPayQuoteRepositoryInterface;
use InPost\InPostPay\Service\ApiConnector\CreateOrUpdateBasket;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Framework\EntityManager\EntityManager as OperationRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\App\AreaInterface;
use InPost\InPostPay\Model\Consumer\Quote\ConsumerQuoteRepository;
use Magento\Store\Model\App\Emulation;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasketCreateOrUpdateConsumer
{
    public function __construct(
        private readonly InPostPayQuoteRepositoryInterface $inPostPayQuoteRepository,
        private readonly OperationRepository $operationRepository,
        private readonly ConsumerQuoteRepository $consumerQuoteRepository,
        private readonly Emulation $emulation,
        private readonly AreaInterface $area,
        private readonly CreateOrUpdateBasket $createOrUpdateBasket,
        private readonly JsonSerializer $jsonSerializer,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(OperationInterface $operation): void
    {
        $basketId = '';
        $serializedData = $operation->getSerializedData();
        if (is_scalar($serializedData)) {
            $basketIdData = $this->jsonSerializer->unserialize((string)$serializedData);
            if (is_array($basketIdData)) {
                $basketId = (string)($basketIdData[InPostPayQuoteInterface::BASKET_ID] ?? '');
            }
        }

        try {
            $this->consumerQuoteRepository->cleanCachedQuotes();
            $this->processBasketExport($basketId);
            $this->handleResult($basketId, $operation);
        } catch (LocalizedException $e) {
            $this->handleResult($basketId, $operation, $e);
        } finally {
            $this->consumerQuoteRepository->cleanCachedQuotes();
        }
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function processBasketExport(string $basketId): void
    {
        $quote = null;
        $inPostPayQuote = $this->inPostPayQuoteRepository->getByBasketId((string)$basketId);
        if (is_scalar($inPostPayQuote->getQuoteId())) {
            $quote = $this->consumerQuoteRepository->get((int)$inPostPayQuote->getQuoteId());
        }

        if ($quote instanceof Quote && $inPostPayQuote->getBrowserId()) {
            try {
                $this->area->load(AreaInterface::PART_TRANSLATE);
                $this->emulation->startEnvironmentEmulation($quote->getStoreId(), 'frontend', true);
                $this->createOrUpdateBasket->execute($quote, $basketId);
                $this->emulation->stopEnvironmentEmulation();
            } catch (LocalizedException $e) {
                $this->emulation->stopEnvironmentEmulation();

                throw $e;
            }
        } else {
            throw new LocalizedException(__(sprintf('Missing basket data for Basket ID: %s.', $basketId)));
        }
    }

    private function handleResult(string $basketId, OperationInterface $operation, ?Exception $exception = null): void
    {
        $topic = (string)$operation->getTopicName();
        if ($exception) {
            $message = sprintf(
                'Consuming failed [%s]. Basked ID: %s Reason: %s',
                $topic,
                $basketId,
                $exception->getMessage()
            );
            $status = OperationInterface::STATUS_TYPE_REJECTED;
            $errorCode = $exception->getCode();
            $this->logger->error($message);
        } else {
            $message = sprintf('Consuming succeeded [%s]. Basked ID %s exported.', $topic, $basketId);
            $this->logger->info($message);
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE);
        $operation->setResultMessage($message);
        if (isset($errorCode)) {
            $operation->setErrorCode($errorCode);
        }

        $this->operationRepository->save($operation);
    }
}
