<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Ui\DataProvider\WeightPrice;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smartcore\InPostInternational\Model\ResourceModel\WeightPrice\Collection;
use Smartcore\InPostInternational\Model\ResourceModel\WeightPrice\CollectionFactory;

class CreateDataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;
    /**
     * @var array<mixed>
     */
    protected array $loadedData = [];

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
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = ['weightprice_fieldset' => $model->getData()];
        }

        $data = $this->dataPersistor->get('weightprice');
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = ['weightprice_fieldset' => $model->getData()];
            $this->dataPersistor->clear('weightprice');
        }

        return $this->loadedData ?: [];
    }
}
