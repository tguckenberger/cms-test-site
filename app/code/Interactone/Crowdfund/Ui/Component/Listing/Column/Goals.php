<?php

namespace Interactone\Crowdfund\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use \Magento\Framework\UrlInterface;

/**
 * Class Goals.
 */
class Goals extends Column
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockRegistry;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StockRegistryInterface $stockRegistry
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StockRegistryInterface $stockRegistry,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $idProduct = $item['entity_id'];
                $stockQty = $this->stockRegistry->getStockItem($idProduct)->getQty();
                $goal = $item[$this->getData('name')];
                $sold = (int) $goal - (int) $stockQty;
                $item[$this->getData('name')] = $sold.'/'.$goal;
            }
        }
        return $dataSource;
    }
}
