<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\DataTransfer\MagentoBestsellerToInPostPayBestsellerDataTransferInterface;
use InvalidArgumentException;

class BestsellerProductDataTransfer
{
    /**
     * @var MagentoBestsellerToInPostPayBestsellerDataTransferInterface[]
     */
    private array $dataTransfers = [];

    public function __construct(
        array $dataTransfer = []
    ) {
        $this->initDataTransfers($dataTransfer);
    }

    public function transfer(
        InPostPayBestsellerProductInterface $magentoBestsellerProduct,
        BestsellerProductInterface $bestsellerProduct
    ): void {
        foreach ($this->dataTransfers as $dataTransfer) {
            $dataTransfer->transfer($magentoBestsellerProduct, $bestsellerProduct);
        }
    }

    /**
     * @param array $dataTransfers
     * @return void
     * @throws InvalidArgumentException
     */
    private function initDataTransfers(array $dataTransfers): void
    {
        foreach ($dataTransfers as $dataTransferKey => $dataTransfer) {
            if ($dataTransfer instanceof MagentoBestsellerToInPostPayBestsellerDataTransferInterface) {
                $this->dataTransfers[$dataTransferKey] = $dataTransfer;
            } else {
                $errorMsg = sprintf('Product to Bestseller data transfer: %s is not valid.', $dataTransferKey);

                throw new InvalidArgumentException($errorMsg);
            }
        }
    }
}
