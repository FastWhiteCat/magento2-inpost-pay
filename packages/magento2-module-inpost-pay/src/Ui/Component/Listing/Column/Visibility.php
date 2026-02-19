<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Ui\Component\Listing\Columns\Column as ParentColumn;

class Visibility extends ParentColumn
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $visibilityValue = InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN;

                if (isset($item[InPostPayCheckoutAgreementInterface::VISIBILITY])) {
                    $visibilityValue = (int)$item[InPostPayCheckoutAgreementInterface::VISIBILITY];
                }

                if ($visibilityValue === InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN) {
                    $item[InPostPayCheckoutAgreementInterface::VISIBILITY] = __('Main Agreement');
                } else {
                    $item[InPostPayCheckoutAgreementInterface::VISIBILITY] = __('Sub-Agreement');
                }
            }
        }

        return $dataSource;
    }
}
