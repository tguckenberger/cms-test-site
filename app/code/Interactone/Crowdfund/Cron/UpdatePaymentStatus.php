<?php

namespace Interactone\Crowdfund\Cron;

use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Interactone\Crowdfund\Helper\Config;
use \Interactone\Crowdfund\Helper\Data;
use \Magento\Store\Model\StoreRepository;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Interactone\Crowdfund\Model\PaypalPayout;
use \Exception;

class UpdatePaymentStatus
{
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
     * @var \Interactone\Crowdfund\Model\PaypalPayout
     */
    protected $payPalPayout;
    
    public function __construct(
        CollectionFactory $productCollectionFactory,
        Config $helperConfig,
        Data $helperData,
        StoreRepository $storeRepository,
        Action $productAction,
        PaypalPayout $payPalPayout
    ) {
        $this->collection = $productCollectionFactory->create();
        $this->helperConfig = $helperConfig;
        $this->helperData = $helperData;
        $this->storeRepository = $storeRepository;
        $this->productAction = $productAction;
        $this->payPalPayout = $payPalPayout;
    }

    public function execute()
    {
        $collection = $this->collection
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', ['eq' => 'designer_product'])
            ->addAttributeToFilter('attribute_set_id', $this->helperConfig->getConfigAttributeSetId());
        
        foreach ($collection as $product) {
            $idProduct = $product->getId();
            if($product->getCampaignPaymentStatus() != '' && $product->getCampaignPaymentStatus() != 'SUCCESS'){
                $paymentStatus = $this->payPalPayout->getPayoutStatus($product->getCampaignPayoutBatchId());
                $this->updateCampaignPaymentStatus($idProduct, $paymentStatus);
                // Send email paid campaign
                $this->helperData->sendCampaignPaidEmail($product);
            }
        }
        return $this;
    }

    protected function updateCampaignPaymentStatus($productId, $status) {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            try {
                $this->productAction->updateAttributes(
                    [$productId],
                    ['campaign_payment_status' => $status],
                    $storeId
                );
            } catch (Exception $e) {
                //TODO: handle exception
            }

        }
    }

}