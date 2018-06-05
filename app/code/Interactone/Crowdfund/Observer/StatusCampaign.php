<?php

namespace Interactone\Crowdfund\Observer;

use \Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use \Magento\Store\Model\StoreRepository;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Interactone\Crowdfund\Helper\Data as HelperData;
use \Interactone\Crowdfund\Model\Campaign;
use \Interactone\Crowdfund\Model\Config\Source\Status;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Exception\LocalizedException;
use \Exception;

class StatusCampaign implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productAction;

    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * */
    protected $date;

    /**
     * @var \Interactone\Crowdfund\Helper\Data
     * */
    protected $helperData;

    /**
     * @var \Interactone\Crowdfund\Model\Campaign
     * */
    protected $campaign;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $productAction
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Interactone\Crowdfund\Helper\Data $helperData
     * @param \Interactone\Crowdfund\Model\Campaign $campaign
     */
    public function __construct(
        ProductAction $productAction,
        StoreRepository $storeRepository,
        ProductRepositoryInterface $productRepository,
        DateTime $date,
        HelperData $helperData,
        Campaign $campaign
    ) {
        $this->productAction = $productAction;
        $this->storeRepository = $storeRepository;
        $this->productRepository = $productRepository;
        $this->date = $date;
        $this->helperData = $helperData;
        $this->campaign = $campaign;
    }

    public function execute(Observer $observer)
    {
        try {
            $stockItem = $observer->getEvent()->getItem();
        } catch (LocalizedException $e) {
            //TODO: handle exception
        }

        if ($stockItem->getIsInStock() != 1) {
            $productInfo = $this->productRepository->getById($stockItem->getProductId());
            $campaignDate = $productInfo->getCampaignEndDate();
            $nowDate = $this->date->gmtDate('Y-d-m');
            if (strtotime($campaignDate) >= strtotime($nowDate)) {
                $this->updateCampaignStatus(
                    $stockItem->getProductId(),
                    Status::STATUS_SUCCESS
                );
                // Send email success campaign
                $this->helperData->sendCampaignSuccessEmail($productInfo);
            } else {
                $this->updateCampaignStatus(
                    $stockItem->getProductId(),
                    Status::STATUS_FAILURE
                );
            }
        }
    }

    protected function updateCampaignStatus($productId, $status)
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            try {
                $this->productAction->updateAttributes(
                    [$productId],
                    ['campaign_status' => $status], $storeId
                );
            } catch (Exception $e) {
                // TODO: handle exception
            }
        }
    }
}
