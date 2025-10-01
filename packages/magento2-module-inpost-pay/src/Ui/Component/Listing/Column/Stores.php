<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Listing\Column;

use InPost\InPostPay\Api\InPostPayCheckoutAgreementRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Ui\Component\Listing\Columns\Column;

class Stores extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository
     * @param Escaper $escaper
     * @param SystemStore $systemStore
     * @param array $components
     * @param array $data
     * @param string|null $storeKey
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly InPostPayCheckoutAgreementRepositoryInterface $inPostPayCheckoutAgreementRepository,
        private readonly Escaper $escaper,
        private readonly SystemStore $systemStore,
        array $components = [],
        array $data = [],
        private readonly ?string $storeKey = 'store_ids'
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareItem(array $item): string
    {
        $content = '';
        if (!empty($item[$this->storeKey])) {
            $origStores = $item[$this->storeKey];
        }

        try {
            if (empty($origStores)) {
                $agreement = $this->inPostPayCheckoutAgreementRepository->get((int)$item['agreement_id']);
                $origStores = $agreement->getStoreIds();
            }
        } catch (NoSuchEntityException $e) {
            $origStores = null;
        }

        if (empty($origStores)) {
            return '';
        }

        if (!is_array($origStores)) {
            $origStores = [$origStores];
        }

        if (in_array(0, $origStores) && count($origStores) == 1) {
            return __('All Store Views')->render();
        }

        $data = $this->systemStore->getStoresStructure(false, $origStores);

        foreach ($data as $website) {
            $content .= $website['label'] . "<br/>";
            foreach ($website['children'] as $group) {
                $groupLabel = $this->escaper->escapeHtml($group['label']);
                $groupLabel = is_scalar($groupLabel) ? (string)$groupLabel : '';
                $content .= str_repeat('&nbsp;', 3) . $groupLabel . "<br/>";

                foreach ($group['children'] as $store) {
                    $storeLabel = $this->escaper->escapeHtml($store['label']);
                    $storeLabel = is_scalar($storeLabel) ? (string)$storeLabel : '';
                    $content .= str_repeat('&nbsp;', 6) . $storeLabel . "<br/>";
                }
            }
        }

        return $content;
    }
}
