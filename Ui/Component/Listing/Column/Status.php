<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Smartcore\InPostInternational\Model\Config\Source\Status as StatusSource;

class Status extends Column
{

    /**
     * @var StatusSource
     */
    protected StatusSource $statusSource;

    /**
     * TrackingNumber constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StatusSource $statusSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StatusSource $statusSource,
        array $components = [],
        array $data = []
    ) {
        $this->statusSource = $statusSource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['status'])) {
                    $item['status'] = $this->statusSource->getStatusLabel($item['status']);
                    if (isset($item['tracking_number_tmp'])) {
                        $item['status'] =
                            '<a target="blank" href="https://inpost.pl/sledzenie-przesylek?number='
                            . $item['tracking_number_tmp'] . '">'
                            . $this->statusSource->getStatusLabel($item['status']) . '</a>';
                    }
                }
            }
        }

        return $dataSource;
    }
}
