<?php

declare(strict_types=1);

namespace InPost\InPostPay\Observer\Product;

use InPost\InPostPay\Service\BestsellerProduct\Upload;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Eav\Model\Entity\Attribute;
use Psr\Log\LoggerInterface;
use Throwable;

class UpdateBestsellerProductAfterAttributeCommitObserver implements ObserverInterface
{
    /**
     * @param Upload $uploadService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Upload $uploadService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $attribute = $observer->getEvent()->getData('attribute');

            if (!$attribute instanceof Attribute) {
                return;
            }

            if ($attribute->getEntityType()->getEntityTypeCode() !== ProductAttributeInterface::ENTITY_TYPE_CODE) {
                return;
            }

            $attributeCode = $attribute->getAttributeCode();
            $attributeCode = is_scalar($attributeCode) && !empty($attributeCode) ? (string)$attributeCode : 'unknown';
            $this->logger->debug(
                sprintf(
                    'Bestseller Products will be uploaded to InPost Pay because attribute [%s] has been saved.',
                    $attributeCode
                )
            );
            $this->uploadService->execute();
            $this->logger->debug(
                sprintf(
                    'Bestseller Products were uploaded to InPost Pay because attribute [%s] has been saved.',
                    $attributeCode
                )
            );
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Error updating bestseller product after attribute [%s] commit: %s',
                    $attributeCode ?? 'unknown',
                    $e->getMessage()
                )
            );
        }
    }
}
