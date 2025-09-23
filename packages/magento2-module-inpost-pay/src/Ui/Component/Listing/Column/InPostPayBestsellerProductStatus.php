<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Enum\InPostBestsellerProductStatus;
use Magento\Ui\Component\Listing\Columns\Column;

class InPostPayBestsellerProductStatus extends Column
{
    public const INPOST_PAY_STATUS = 'inpost_pay_status';
    private const ACTIVE_STATUS_LABEL = 'Active [Approved by InPost]';
    private const INACTIVE_STATUS_LABEL = 'Inactive [InPost Approval Required]';
    private const UNKNOWN_STATUS_LABEL = 'Unknown [Synchronization Required]';

    private array $inPostPayStatusMap = [
        InPostBestsellerProductStatus::INACTIVE_VALUE => self::INACTIVE_STATUS_LABEL,
        InPostBestsellerProductStatus::ACTIVE_VALUE => self::ACTIVE_STATUS_LABEL,
        InPostBestsellerProductStatus::UNKNOWN_VALUE => self::UNKNOWN_STATUS_LABEL,
    ];

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[InPostPayBestsellerProductInterface::INPOST_PAY_STATUS])) {
                    $status = (string)$item[InPostPayBestsellerProductInterface::INPOST_PAY_STATUS];
                } else {
                    $status = InPostBestsellerProductStatus::UNKNOWN->value;
                }

                $label = $this->inPostPayStatusMap[$status] ?? self::UNKNOWN_STATUS_LABEL;
                $item[self::INPOST_PAY_STATUS] = __($label);
            }
        }

        return $dataSource;
    }
}
