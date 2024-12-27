<?php
namespace Smartcore\InPostInternational\Ui\DataProvider\Shipment;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Smartcore\InPostInternational\Model\ResourceModel\Shipment\CollectionFactory;

class ListingDataProvider extends AbstractDataProvider
{
    /**
     * ListingDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
