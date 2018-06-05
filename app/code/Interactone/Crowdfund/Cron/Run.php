<?php

namespace Interactone\Crowdfund\Cron;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Catalog\Model\CategoryFactory;
use \Interactone\Crowdfund\Helper\Config;
use \Interactone\Crowdfund\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Store\Model\StoreRepository;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Interactone\Crowdfund\Model\Config\Source\Status;
use \Interactone\Crowdfund\Model\Config\Source\Status as CampaignStatus;
use \Exception;

class Run
{
    /**
     * @var $categoryFactory
     * */
    protected $categoryFactory;

    /**
     * @var $helperConfig
     * */
    protected $helperConfig;

    /**
     * @var $helperData
     * */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productAction;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * */
    protected $date;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        Config $helperConfig,
        Data $helperData,
        StoreRepository $storeRepository,
        Action $productAction,
        StockRegistryInterface $stockRegistry,
        DateTime $date
    ) {
        $this->collection = $productCollectionFactory->create();
        $this->categoryFactory = $categoryFactory;
        $this->helperConfig = $helperConfig;
        $this->helperData = $helperData;
        $this->storeRepository = $storeRepository;
        $this->productAction = $productAction;
        $this->stockRegistry = $stockRegistry;
        $this->date = $date;
    }

    public function execute()
    {
        $idCategoryCampaign = $this->helperConfig->getConfigCampaignCategoryId();
        $category = $this->categoryFactory->create()->load($idCategoryCampaign);

        $collection = $this->collection
            ->addAttributeToSelect('*')
            ->addCategoryFilter($category)
            ->addAttributeToFilter('type_id', array('eq' => 'designer_product'))
            ->addAttributeToFilter('attribute_set_id', $this->helperConfig->getConfigAttributeSetId());
        
        foreach ($collection as $product) {
            $idProduct = $product->getId();
            $skuProduct = $product->getSku();

            $campaignDate = $product->getCampaignEndDate();
            $nowDate = $this->date->gmtDate('Y-m-d');
            if (
                strtotime($campaignDate) < strtotime($nowDate) &&
                ($product->getCampaignStatus() == CampaignStatus::STATUS_PROCESSING)
            ) {
                $stockItem = $this->stockRegistry->getStockItem($idProduct);
                $stockItem->setData('is_in_stock', 0);
                try {
                    $this->stockRegistry->updateStockItemBySku($skuProduct, $stockItem);
                } catch (NoSuchEntityException $e) {
                    //TODO: handle exception
                }
                $this->updateCampaignStatus($idProduct,Status::STATUS_FAILURE);
                // Send email fail campaign
                $this->helperData->sendCampaignFailEmail($product);
            }
        }
        return $this;
    }

    protected function updateCampaignStatus($productId, $status)
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            try {
                $this->productAction->updateAttributes(
                    [$productId],
                    ['campaign_status' => $status], $storeId);
            } catch (Exception $e) {
                // TODO: Handle exception
            }
        }
    }
}
