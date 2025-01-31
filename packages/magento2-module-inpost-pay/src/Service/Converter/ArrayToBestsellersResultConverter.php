<?php

declare(strict_types=1);

namespace InPost\InPostPay\Service\Converter;

use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProductInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellersInterfaceFactory;
use InPost\InPostPay\Enum\InPostQuantityType;
use InPost\InPostPay\Model\IziApi\Request\GetBestsellersRequestFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterface;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\ProductAvailabilityInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\BestsellerProduct\BestsellerQuantityInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\PriceInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\ProductAttributeInterfaceFactory;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterface;
use InPost\InPostPay\Api\Data\Merchant\Basket\Product\AdditionalImageInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ArrayToBestsellersResultConverter
{
    /**
     * @param BestsellersInterfaceFactory $bestsellersFactory
     * @param BestsellerProductInterfaceFactory $bestsellerProductFactory
     * @param ProductAvailabilityInterfaceFactory $productAvailabilityFactory
     * @param ProductAttributeInterfaceFactory $productAttributeFactory
     * @param AdditionalImageInterfaceFactory $additionalImageFactory
     */
    public function __construct(
        private readonly BestsellersInterfaceFactory $bestsellersFactory,
        private readonly BestsellerProductInterfaceFactory $bestsellerProductFactory,
        private readonly ProductAvailabilityInterfaceFactory $productAvailabilityFactory,
        private readonly ProductAttributeInterfaceFactory $productAttributeFactory,
        private readonly AdditionalImageInterfaceFactory $additionalImageFactory,
    ) {
    }

    /**
     * @param array $resultArray
     * @return BestsellersInterface
     */
    public function convert(array $resultArray): BestsellersInterface
    {
        /** @var BestsellersInterface $bestsellersResult */
        $bestsellersResult = $this->bestsellersFactory->create();

        $pageIndex = $resultArray[BestsellersInterface::PAGE_INDEX] ?? null;
        $pageSize = $resultArray[BestsellersInterface::PAGE_SIZE] ?? null;
        $totalItems = $resultArray[BestsellersInterface::TOTAL_ITEMS] ?? null;
        $contentProducts = $resultArray[BestsellersInterface::CONTENT] ?? [];

        $bestsellersResult->setPageIndex($pageIndex);
        $bestsellersResult->setPageSize($pageSize);
        $bestsellersResult->setTotalItems($totalItems);
        $bestsellerProducts = [];

        foreach ($contentProducts as $productData) {
            $bestsellerProducts[] = $this->convertBestsellerProductResult((array)$productData);
        }

        $bestsellersResult->setContent($bestsellerProducts);

        return $bestsellersResult;
    }

    /**
     * @param array $productData
     * @return BestsellerProductInterface
     */
    private function convertBestsellerProductResult(array $productData): BestsellerProductInterface
    {
        /** @var BestsellerProductInterface $bestsellerProduct */
        $bestsellerProduct = $this->bestsellerProductFactory->create();

        $productId = $productData[BestsellerProductInterface::PRODUCT_ID] ?? '';
        $status = $productData[BestsellerProductInterface::STATUS] ?? '';
        $ean = $productData[BestsellerProductInterface::EAN] ?? '';
        $qrCode = $productData[BestsellerProductInterface::QR_CODE] ?? '';
        $deepLink = $productData[BestsellerProductInterface::DEEP_LINK] ?? '';
        $productAvailabilityData = $productData[BestsellerProductInterface::PRODUCT_AVAILABILITY] ?? [];
        $productName = $productData[BestsellerProductInterface::PRODUCT_NAME] ?? '';
        $productDescription = $productData[BestsellerProductInterface::PRODUCT_DESCRIPTION] ?? '';
        $productImage = $productData[BestsellerProductInterface::PRODUCT_IMAGE] ?? '';
        $additionalImagesData = $productData[BestsellerProductInterface::ADDITIONAL_PRODUCT_IMAGES] ?? [];
        $priceData = $productData[BestsellerProductInterface::PRICE] ?? [];
        $currency = $productData[BestsellerProductInterface::CURRENCY] ?? BestsellerProductInterface::DEFAULT_CURRENCY;
        $quantityData = $productData[BestsellerProductInterface::QUANTITY] ?? [];
        $productAttributesData = $productData[BestsellerProductInterface::PRODUCT_ATTRIBUTES] ?? [];

        $bestsellerProduct->setProductId($productId);
        $bestsellerProduct->setEan($ean);
        $bestsellerProduct->setQrCode($qrCode);
        $bestsellerProduct->setDeepLink($deepLink);
        $bestsellerProduct->setProductName($productName);
        $bestsellerProduct->setProductDescription($productDescription);
        $bestsellerProduct->setProductImage($productImage);
        $bestsellerProduct->setCurrency($currency);
        $bestsellerProduct->setProductAvailability($this->convertAvailability((array)$productAvailabilityData));
        $bestsellerProduct->setAdditionalProductImages(
            $this->convertAdditionalImages((array)$additionalImagesData)
        );
        $bestsellerProduct->setProductAttributes(
            $this->convertProductAttributes((array)$productAttributesData)
        );
        $price = $this->convertPrice($bestsellerProduct->getPrice(), (array)$priceData);
        $bestsellerProduct->setPrice($price);
        $quantity = $this->convertQuantity($bestsellerProduct->getQuantity(), (array)$quantityData);
        $bestsellerProduct->setQuantity($quantity);

        if ($status) {
            $bestsellerProduct->setStatus($status);
        }

        return $bestsellerProduct;
    }

    /**
     * @param array $availabilityData
     * @return ProductAvailabilityInterface|null
     */
    private function convertAvailability(array $availabilityData): ?ProductAvailabilityInterface
    {
        if (empty($availabilityData)) {
            return null;
        }

        /** @var ProductAvailabilityInterface $productAvailability */
        $productAvailability = $this->productAvailabilityFactory->create();

        $startDate = $availabilityData[ProductAvailabilityInterface::START_DATE] ?? null;
        $endDate = $availabilityData[ProductAvailabilityInterface::END_DATE] ?? null;

        $productAvailability->setStartDate($startDate);
        $productAvailability->setEndDate($endDate);

        return $productAvailability;
    }

    /**
     * @param array $additionalImagesData
     * @return AdditionalImageInterface[]
     */
    private function convertAdditionalImages(array $additionalImagesData): array
    {
        $additionalImages = [];

        foreach ($additionalImagesData as $additionalImageData) {
            $smallSize = $additionalImageData[AdditionalImageInterface::SMALL_SIZE] ?? null;
            $normalSize = $additionalImageData[AdditionalImageInterface::NORMAL_SIZE] ?? null;

            if ($smallSize && $normalSize) {
                /** @var AdditionalImageInterface $additionalImage */
                $additionalImage = $this->additionalImageFactory->create();
                $additionalImage->setSmallSize((string)$smallSize);
                $additionalImage->setNormalSize((string)$normalSize);

                $additionalImages[] = $additionalImage;
            }
        }

        return $additionalImages;
    }

    /**
     * @param array $productAttributesData
     * @return ProductAttributeInterface[]
     */
    private function convertProductAttributes(array $productAttributesData): array
    {
        $productAttributes = [];

        foreach ($productAttributesData as $productAttributeData) {
            $name = $productAttributeData[ProductAttributeInterface::ATTRIBUTE_NAME] ?? null;
            $value = $productAttributeData[ProductAttributeInterface::ATTRIBUTE_VALUE] ?? null;

            if ($name && $value) {
                /** @var ProductAttributeInterface $productAttribute */
                $productAttribute = $this->productAttributeFactory->create();
                $productAttribute->setAttributeName((string)$name);
                $productAttribute->setAttributeValue((string)$value);

                $productAttributes[] = $productAttribute;
            }
        }

        return $productAttributes;
    }

    /**
     * @param PriceInterface $price
     * @param array $priceData
     * @return PriceInterface
     */
    private function convertPrice(PriceInterface $price, array $priceData): PriceInterface
    {
        $net = (float)($priceData[PriceInterface::NET] ?? 0.00);
        $gross = (float)($priceData[PriceInterface::GROSS] ?? 0.00);
        $vat = (float)($priceData[PriceInterface::VAT] ?? 0.00);

        $price->setNet($net);
        $price->setGross($gross);
        $price->setVat($vat);

        return $price;
    }

    private function convertQuantity(
        BestsellerQuantityInterface $quantity,
        array $quantityData
    ): BestsellerQuantityInterface {
        $availableQuantity = (float)($quantityData[BestsellerQuantityInterface::AVAILABLE_QUANTITY] ?? 0);
        $quantityType = $quantityData[BestsellerQuantityInterface::QUANTITY_TYPE] ?? null;
        $quantityUnit = $quantityData[BestsellerQuantityInterface::QUANTITY_UNIT] ?? null;

        if ($quantityType === null) {
            $quantityType = InPostQuantityType::INTEGER->value;
        }

        $quantity->setAvailableQuantity($availableQuantity);
        $quantity->setQuantityType($quantityType);
        $quantity->setQuantityUnit($quantityUnit);

        return $quantity;
    }
}
