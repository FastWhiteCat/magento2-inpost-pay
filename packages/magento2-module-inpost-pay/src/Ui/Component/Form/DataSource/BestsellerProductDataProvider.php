<?php

declare(strict_types=1);

namespace InPost\InPostPay\Ui\Component\Form\DataSource;

use InPost\InPostPay\Api\Data\InPostPayBestsellerProductInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use InPost\InPostPay\Model\ResourceModel\InPostPayBestsellerProduct\CollectionFactory;

class BestsellerProductDataProvider extends AbstractDataProvider
{
    private array $loadedData = [];
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
        }

        $data = $this->dataPersistor->get(InPostPayBestsellerProductInterface::ENTITY_NAME);

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            // @phpstan-ignore-next-line
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear(InPostPayBestsellerProductInterface::ENTITY_NAME);
        }

        return $this->loadedData;
    }
}
