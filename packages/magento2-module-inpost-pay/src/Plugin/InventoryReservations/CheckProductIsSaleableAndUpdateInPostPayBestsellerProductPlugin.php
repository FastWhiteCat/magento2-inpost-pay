<?php

declare(strict_types=1);

namespace InPost\InPostPay\Plugin\InventoryReservations;

use InPost\InPostPay\Observer\Product\UpdateInPostPayBestsellerProductAfterSaveObserver;
use InPost\InPostPay\Service\BestsellerProduct\BestsellerChecker;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Throwable;

class CheckProductIsSaleableAndUpdateInPostPayBestsellerProductPlugin
{
    /**
     * @param BestsellerChecker $bestsellerChecker
     * @param ProductRepositoryInterface $productRepository
     * @param UpdateInPostPayBestsellerProductAfterSaveObserver $updateInPostPayBestsellerObserver
     */
    public function __construct(
        private readonly BestsellerChecker $bestsellerChecker,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly UpdateInPostPayBestsellerProductAfterSaveObserver $updateInPostPayBestsellerObserver
    ) {
    }

    /**
     * @param AppendReservationsInterface $subject
     * @param $result
     * @param ReservationInterface[] $reservations
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute( // @phpstan-ignore-line
        AppendReservationsInterface $subject,
        $result,
        array $reservations
    ): void {
        foreach ($reservations as $reservation) {
            $sku = $reservation->getSku();

            if (!$this->bestsellerChecker->isBestsellerProductBySku($sku)) {
                continue;
            }

            try {
                $product = $this->productRepository->get($sku);

                if ($product instanceof Product && !$product->isSaleable()) {
                    $this->updateInPostPayBestsellerObserver->updateBestsellerProduct($product);
                }
            } catch (Throwable $e) {
                continue;
            }
        }
    }
}
