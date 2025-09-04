<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\DataTransfer\ProductToInPostProduct;

use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\ProductInterface;
use Magento\Catalog\Model\Product;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterfaceFactory;
use InPost\InPostPay\Provider\Config\GeneralConfigProvider;
use InPost\InPostPay\Provider\Product\AdditionalImagesProvider;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Store\Model\App\Emulation;

class AdditionalProductImagesDataTransfer
{
    public const MAX_ADDITIONAL_IMAGES_COUNT = 10;

    public function __construct(
        private readonly ImageHelper $imageHelper,
        private readonly GeneralConfigProvider $generalConfigProvider,
        private readonly Emulation $emulation,
        private readonly AdditionalImageInterfaceFactory $additionalImageFactory,
        private readonly AdditionalImagesProvider $additionalImagesProvider
    ) {
    }

    public function transfer(
        Product $product,
        ProductInterface $inPostProduct
    ): void {
        if (!$this->generalConfigProvider->isAdditionalImagesEnabled((int)$product->getStoreId())) {
            return;
        }

        $inPostProduct->setAdditionalProductImages(
            $this->getAdditionalProductImages($product, $inPostProduct)
        );
    }

    /**
     * @param Product $product
     * @param ProductInterface $inPostProduct
     * @return array
     */
    private function getAdditionalProductImages(
        Product $product,
        ProductInterface $inPostProduct
    ): array {
        $storeId = (int)$product->getStoreId();
        $mediaGalleryImages = $this->getMediaGalleryImages($product);

        if (empty($mediaGalleryImages)) {
            return [];
        }

        $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);

        $images = [];
        $totalImages = 0;
        $firstAdditionalImage = null;
        foreach ($mediaGalleryImages as $galleryImage) {
            // @phpstan-ignore-next-line
            $file = (string)$galleryImage->getFile();

            if ($totalImages >= self::MAX_ADDITIONAL_IMAGES_COUNT) {
                break;
            }

            if (empty($file)) {
                continue;
            }

            $normalImage = $galleryImage->getUrl(); // @phpstan-ignore-line
            $smallImage = $galleryImage->getUrl(); // @phpstan-ignore-line

            if ($this->generalConfigProvider->isPrepareResizedImagesEnabled($storeId)) {
                $smallImage = $this->prepareResizedImageUrl(
                    $product,
                    $file,
                    AdditionalImageInterface::SMALL_SIZE_WIDTH,
                    AdditionalImageInterface::SMALL_SIZE_HEIGHT
                );

                $normalImage = $this->prepareResizedImageUrl(
                    $product,
                    $file,
                    AdditionalImageInterface::NORMAL_SIZE_WIDTH,
                    AdditionalImageInterface::NORMAL_SIZE_HEIGHT
                );
            }

            $additionalImage = $this->additionalImageFactory->create();
            $additionalImage->setSmallSize($smallImage ?? '');
            $additionalImage->setNormalSize($normalImage ?? '');

            if ($normalImage === $inPostProduct->getProductImage()) {
                $firstAdditionalImage = $additionalImage;

                continue;
            }

            $images[] = $additionalImage;
            $totalImages++;
        }

        $this->emulation->stopEnvironmentEmulation();

        return array_slice(
            $firstAdditionalImage ? array_merge([$firstAdditionalImage], $images) : $images,
            0,
            self::MAX_ADDITIONAL_IMAGES_COUNT
        );
    }

    private function getMediaGalleryImages(Product $product): array
    {
        $mediaGalleryImages = $this->additionalImagesProvider->execute($product);

        if ((empty($mediaGalleryImages->getItems()))
            && $product->hasData(ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT)
            && $product->getData(ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT) instanceof Product
        ) {
            /** @var Product $product */
            $product = $product->getData(ProductToInPostProductDataTransfer::CONFIGURABLE_PARENT_PRODUCT);
            $mediaGalleryImages = $this->additionalImagesProvider->execute($product);
        }

        return $mediaGalleryImages->getItems();
    }

    private function prepareResizedImageUrl(Product $product, string $file, int $width, int $height): string
    {
        return $this->imageHelper->init($product, 'product_page_image_large')
            ->setImageFile($file)
            ->constrainOnly(false)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->resize($width, $height)
            ->getUrl();
    }
}
