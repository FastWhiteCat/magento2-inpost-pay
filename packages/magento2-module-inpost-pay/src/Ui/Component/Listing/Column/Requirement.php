<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\Data\InPostPayCheckoutAgreementInterface;
use Magento\Ui\Component\Listing\Columns\Column as ParentColumn;

class Requirement extends ParentColumn
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $visibilityValue = InPostPayCheckoutAgreementInterface::VISIBILITY_MAIN;

                if (isset($item[InPostPayCheckoutAgreementInterface::VISIBILITY])) {
                    $visibilityValue = (int)$item[InPostPayCheckoutAgreementInterface::VISIBILITY];
                }

                $requirementValue = InPostPayCheckoutAgreementInterface::REQUIREMENT;

                if (isset($item[InPostPayCheckoutAgreementInterface::REQUIREMENT])) {
                    $requirementValue = (string)$item[InPostPayCheckoutAgreementInterface::REQUIREMENT];
                }

                if ($visibilityValue === InPostPayCheckoutAgreementInterface::VISIBILITY_CHILD) {
                    $requirementValue = '';
                }

                $item[InPostPayCheckoutAgreementInterface::REQUIREMENT] = $requirementValue;
            }
        }

        return $dataSource;
    }
}
