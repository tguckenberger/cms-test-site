<?php

namespace Interactone\Crowdfund\Block;

use \Magento\Framework\View\Element\Template;
use \Magento\Customer\Model\Session;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Catalog\Model\CategoryFactory;
use \Interactone\Crowdfund\Helper\Config;
use \Magento\Catalog\Helper\Image;
use \Magento\Framework\Pricing\Helper\Data;
use \Magento\CatalogInventory\Api\StockRegistryInterface;

class View extends Template
{
    /**
     * @var $session
     * */
    protected $session;

    /**
     * @var $categoryFactory
     * */
    protected $categoryFactory;

    /**
     * @var $helperConfig
     * */
    protected $helperConfig;

    /**
     * @var $helperImageProduct
     * */
    protected $helperImageProduct;

    /**
     * @var $helperImageProduct
     * */
    protected $helperPrice;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockRegistry;
    
    public function __construct(
        Session $session,
        Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        Config $helperConfig,
        Image $helperImageProduct,
        Data $helperPrice,
        StockRegistryInterface $stockRegistry
    ) {
        $this->session = $session;
        $this->collection = $productCollectionFactory->create();
        $this->categoryFactory = $categoryFactory;
        $this->helperConfig = $helperConfig;
        $this->helperImageProduct = $helperImageProduct;
        $this->helperPrice = $helperImageProduct = $helperPrice;
        $this->stockRegistry = $stockRegistry;
        return parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProductCampaignByIdCustomer()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'interactone.products.campaign.pager'
            )->setShowPerPage(true)->setCollection(
                $this->getProductCampaignByIdCustomer()
            );
            $this->setChild('pager', $pager);
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getProductCampaignByIdCustomer()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;

        $idCategoryCampaign = $this->helperConfig->getConfigCampaignCategoryId();
        $category = $this->categoryFactory->create()->load($idCategoryCampaign);

        $collection = $this->collection
            ->addAttributeToSelect('*')
            ->addCategoryFilter($category)
            ->addAttributeToFilter('campaign_customer_id', $this->session->getId())
            ->addAttributeToFilter('type_id', ['eq' => 'designer_product'])
            ->addAttributeToFilter('attribute_set_id', $this->helperConfig->getConfigAttributeSetId())
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setOrder('created_at','DESC')
            ->setPageSize($pageSize)
            ->setCurPage($page);
        $collection->setFlag('has_stock_status_filter', true);
        return $collection;
    }

    public function getImageProduct($product, $type = 'category_page_list', $resize = 100)
    {
        return $this->helperImageProduct
            ->init($product, $type)
            ->constrainOnly(false)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->resize($resize)
            ->getUrl();
    }

    public function getPriceProduct($price)
    {
        return $this->helperPrice->currency($price, true, false);
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getGoals($product)
    {
        $goal = $product->getCampaignGoals();
        $stockQty = $this->stockRegistry->getStockItem($product->getId())->getQty();
        $sold = (int) $goal - (int) $stockQty;

        return $sold.'/'.$goal;
    }
}
