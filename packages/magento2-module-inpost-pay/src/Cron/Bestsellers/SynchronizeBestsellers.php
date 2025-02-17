<?php
declare(strict_types=1);

namespace InPost\InPostPay\Cron\Bestsellers;

use InPost\InPostPay\Model\Config\Source\BestsellersSynchronizeMode;
use InPost\InPostPay\Provider\Config\BestsellersCronConfigProvider;
use InPost\InPostPay\Service\BestsellerProduct\Download;
use InPost\InPostPay\Service\BestsellerProduct\Upload;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class SynchronizeBestsellers
{
    public function __construct(
        private readonly BestsellersCronConfigProvider $bestsellersCronConfigProvider,
        private readonly Download $download,
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
            $mode = $this->bestsellersCronConfigProvider->getSynchronizationMode();

            try {
                if ($mode === BestsellersSynchronizeMode::DOWNLOAD) {
                    $this->download->execute();
                } elseif ($mode === BestsellersSynchronizeMode::UPLOAD) {
                    $this->upload->execute();
                }

                $this->logger->error(
                    sprintf('Bestsellers Synchronization CRON Job [mode:%s] success!', $mode)
                );
            } catch (LocalizedException $e) {
                $this->logger->error(
                    sprintf('Bestsellers Synchronization CRON Job [mode:%s] error: %s', $mode, $e->getMessage())
                );
            }
        }
    }
}
