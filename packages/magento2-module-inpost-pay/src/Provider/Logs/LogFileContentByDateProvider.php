<?php

declare(strict_types=1);

namespace InPost\InPostPay\Provider\Logs;

use InPost\InPostPay\Logger\Handler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;

class LogFileContentByDateProvider
{
    /**
     * @param DirectoryList $directoryList
     * @param File $driverFile
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $driverFile
    ) {
    }

    /**
     * @param string $date
     * @param bool|null $encode
     * @return string
     * @throws FileSystemException
     */
    public function getContent(string $date, ?bool $encode = true): string
    {
        $varDir = $this->directoryList->getPath(DirectoryList::VAR_DIR);
        $path = sprintf(
            '/%s/log/%s/%s.log',
            trim($varDir, '/'),
            Handler::INPOST_PAY_LOG_CATALOG,
            $date
        );

        $content =  $this->driverFile->fileGetContents($path);

        return $encode ? base64_encode($content) : $content;
    }
}
