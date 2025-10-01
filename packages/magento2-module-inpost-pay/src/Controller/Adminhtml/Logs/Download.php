<?php

declare(strict_types=1);

namespace InPost\InPostPay\Controller\Adminhtml\Logs;

use InPost\InPostPay\Block\Adminhtml\System\Config\Form\RecentLogs;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Throwable;

class Download extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'InPost_InPostPay::inpostpay';

    /**
     * @param Context $context
     * @param RecentLogs $recentLogs
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        private readonly RecentLogs $recentLogs,
        private readonly FileFactory $fileFactory,
        private readonly Filesystem $filesystem
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        try {
            $logDate = $this->getRequest()->getParam('log_date', date(RecentLogs::LOGS_DATE_FORMAT));
            $logDate = is_scalar($logDate) ? (string)$logDate : '';
            $this->recentLogs->validateLogDate($logDate);
            $logFileAbsolutePath = $this->recentLogs->getLogAbsolutePathByDate($logDate);
            $this->recentLogs->validateLogFilePath($logFileAbsolutePath);
            $content = $this->recentLogs->getLogsContent($logFileAbsolutePath);
            $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
            $tmpFilePath = sprintf('tmp_inpost_pay_logs_%s_%s.log', time(), $logDate);
            $tmpDir->writeFile($tmpFilePath, $content);

            // @phpstan-ignore-next-line
            return $this->fileFactory->create(
                sprintf('%s.log', $logDate), //@phpstan-ignore-line
                [
                    'type' => 'filename',
                    'value' => $tmpFilePath,
                    'rm' => true
                ],
                DirectoryList::TMP
            );
        } catch (Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect(
                'adminhtml/system_config/edit',
                ['section' => 'payment']
            );
        }
    }
}
