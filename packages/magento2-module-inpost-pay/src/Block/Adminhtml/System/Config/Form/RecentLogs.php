<?php

declare(strict_types=1);

namespace InPost\InPostPay\Block\Adminhtml\System\Config\Form;

use DateTime;
use InPost\InPostPay\Logger\Handler;
use InvalidArgumentException;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Throwable;

class RecentLogs extends Field
{
    public const LOGS_DATE_FORMAT = 'Y-m-d';
    private const RECENT_LOGS_DAYS_COUNT = 5;
    private const ACTION_URL = 'inpostpay/logs/download';

    private UrlInterface $urlBuilder;

    /**
     * @param DirectoryList $directoryList
     * @param File $driver
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly File $driver,
        Context $context,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    protected function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('InPost_InPostPay::system/config/form/recent-logs.phtml');
    }

    /**
     * @return array
     */
    public function getRecentLogDates(): array
    {
        $dates = [];
        $date = new DateTime();

        for ($i = 0; $i < self::RECENT_LOGS_DAYS_COUNT; $i++) {
            $formattedDate = $date->format(self::LOGS_DATE_FORMAT);
            $filePath = $this->getLogAbsolutePathByDate($formattedDate);

            try {
                $this->validateLogFilePath($filePath);
                $dates[] = $formattedDate;
                $date->modify('-1 day');
            } catch (InvalidArgumentException $e) {
                $date->modify('-1 day');
            }
        }

        return $dates;
    }

    /**
     * @param string $logDate
     * @return string
     */
    public function getDownloadLogButtonHtml(string $logDate): string
    {
        try {
            $this->validateLogDate($logDate);
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'id' => 'inpostpay_debugging_log_download_button_' . $logDate,
                    'label' => __('%1.log', $logDate),
                    'onclick' => $this->getOnClickAction($logDate)
                ]
            );

            return $button->toHtml();
        } catch (LocalizedException $e) {
            return '';
        }
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param string $logDate
     * @return string
     */
    public function getLogAbsolutePathByDate(string $logDate): string
    {
        try {
            $logFileAbsolutePath = sprintf(
                '/%s/%s/%s/%s.log',
                trim($this->directoryList->getPath(DirectoryList::VAR_DIR), '/'),
                DirectoryList::LOG,
                Handler::INPOST_PAY_LOG_CATALOG,
                $logDate
            );
        } catch (FileSystemException $e) {
            $logFileAbsolutePath = '';
        }

        return $logFileAbsolutePath;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        try {
            return $this->_toHtml();
        } catch (Throwable $e) {
            return '';
        }
    }

    /**
     * @param string $logDate
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateLogDate(string $logDate): void
    {
        $dateTime = DateTime::createFromFormat(self::LOGS_DATE_FORMAT, $logDate);

        if (!$dateTime || $dateTime->format(self::LOGS_DATE_FORMAT) !== $logDate) {
            throw new InvalidArgumentException(
                __('Invalid date format. Required format is YYYY-MM-DD (e.g., 2025-08-04)')->render()
            );
        }

        if (!in_array($logDate, $this->getRecentLogDates())) {
            throw new InvalidArgumentException(__('Log date out of range.')->render());
        }
    }

    /**
     * @param string $logFilePath
     * @return string
     */
    public function getLogsContent(string $logFilePath): string
    {
        try {
            return $this->driver->fileGetContents($logFilePath);
        } catch (FileSystemException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $logFilePath
     * @return void
     * @throws InvalidArgumentException
     */
    public function validateLogFilePath(string $logFilePath): void
    {
        try {
            if (!$this->driver->isFile($logFilePath)) {
                throw new InvalidArgumentException(__('InPost Pay log file: %1 not found.', $logFilePath)->render());
            }
        } catch (FileSystemException $e) {
            throw new InvalidArgumentException(
                __(
                    'InPost Pay log file: %1 could not be checked if exists. Reason: %2',
                    $logFilePath,
                    $e->getMessage()
                )->render()
            );
        }

        try {
            if (!$this->driver->isReadable($logFilePath)) {
                throw new InvalidArgumentException(
                    __('InPost Pay log file: %1 is not readable.', $logFilePath)->render()
                );
            }
        } catch (FileSystemException $e) {
            throw new InvalidArgumentException(
                __(
                    'InPost Pay log file: %1 could not be checked if is readable. Reason: %2',
                    $logFilePath,
                    $e->getMessage()
                )->render()
            );
        }
    }

    private function getOnClickAction(string $logDate): string
    {
        return sprintf(
            "location.href = '%s'",
            $this->urlBuilder->getUrl(self::ACTION_URL, ['log_date' => $logDate])
        );
    }
}
