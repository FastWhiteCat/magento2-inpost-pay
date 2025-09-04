<?php

declare(strict_types=1);

namespace InPost\InPostPay\Validator;

use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory as LinkCollectionFactory;

/**
 * Checks if guest checkout is allowed for quote that contains virtual products.
 * See: \Magento\Downloadable\Observer\IsAllowedGuestCheckoutObserver
 */
class DigitalQuoteValidator
{
    private const XML_PATH_DISABLE_GUEST_CHECKOUT = 'catalog/downloadable/disable_guest_checkout';
    private const XML_PATH_DOWNLOADABLE_SHAREABLE = 'catalog/downloadable/shareable';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LinkCollectionFactory $linkCollectionFactory
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LinkCollectionFactory $linkCollectionFactory
    ) {
    }

    /**
     * @param Quote $quote
     * @return bool
     */
    public function isDigitalQuoteAllowed(Quote $quote): bool
    {
        if (!$quote->getCustomerIsGuest()) {
            return true;
        }

        $isAllowed = true;
        $storeId = $quote->getStoreId();
        $isGuestCheckoutDisabled = $this->isLoggedInAccountRequiredForDigitalQuotes($storeId);

        foreach ($quote->getAllItems() as $item) {
            /** @var Item $item */
            $product = $item->getProduct();
            $typeId = is_scalar($product->getTypeId()) ? (string)$product->getTypeId() : '';

            if (!$product->isVirtual()) {
                continue;
            }

            if ($isGuestCheckoutDisabled) {
                $isAllowed = false;
                break;
            }

            if ($typeId === Type::TYPE_DOWNLOADABLE && !$this->checkForShareableLinks($item, $storeId)) {
                $isAllowed = false;
                break;
            }
        }

        return $isAllowed;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isLoggedInAccountRequiredForDigitalQuotes(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISABLE_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param Item $item
     * @param int $storeId
     * @return bool
     */
    private function checkForShareableLinks(Item $item, int $storeId): bool
    {
        $isSharable = true;
        $option = $item->getOptionByCode('downloadable_link_ids');

        // @phpstan-ignore-next-line
        if (!empty($option)) {
            $optionValue = is_scalar($option->getValue()) ? (string)$option->getValue() : '';
            $downloadableLinkIds = explode(',', $optionValue);
            $linkCollection = $this->linkCollectionFactory->create();
            $linkCollection->addFieldToFilter('link_id', ['in' => $downloadableLinkIds]);
            $linkCollection->addFieldToFilter('is_shareable', ['in' => $this->getNotSharableValues($storeId)]);
            $isSharable = $linkCollection->getSize() === 0;
        }

        return $isSharable;
    }

    /**
     * @param int $storeId
     * @return array
     */
    private function getNotSharableValues(int $storeId): array
    {
        $configIsSharable = $this->scopeConfig->isSetFlag(
            self::XML_PATH_DOWNLOADABLE_SHAREABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $notShareableValues = [Link::LINK_SHAREABLE_NO];

        if (!$configIsSharable) {
            $notShareableValues[] = Link::LINK_SHAREABLE_CONFIG;
        }

        return $notShareableValues;
    }
}
