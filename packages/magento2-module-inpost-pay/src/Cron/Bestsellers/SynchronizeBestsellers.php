<?php
declare(strict_types=1);

namespace InPost\InPostPay\Cron\Bestsellers;

use InPost\InPostPay\Provider\Config\BestsellersCronConfigProvider;
use InPost\InPostPay\Service\BestsellerProduct\Upload;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SynchronizeBestsellers
{
    public function __construct(
        private readonly BestsellersCronConfigProvider $bestsellersCronConfigProvider,
        private readonly Upload $upload,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        if ($this->bestsellersCronConfigProvider->isCronEnabled()) {
            try {
                $this->upload->execute();
                $this->logger->debug('Bestsellers Synchronization CRON Job success!');
            } catch (LocalizedException $e) {
                $this->logger->error(
                    sprintf('Bestsellers Synchronization CRON Job error: %s', $e->getMessage())
                );
            }
        }
    }
}
