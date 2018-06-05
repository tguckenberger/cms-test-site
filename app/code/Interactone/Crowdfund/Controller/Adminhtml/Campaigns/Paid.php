<?php

namespace Interactone\Crowdfund\Controller\Adminhtml\Campaigns;

use \PayPal\Exception\PayPalConnectionException;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Interactone\Crowdfund\Model\PaypalPayout;
use \Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use \Magento\Store\Model\StoreRepository;
use \Exception;

class Paid extends Action
{
    protected $resultPageFactory = false;
    protected $resultPage;
    protected $productRepository;
    protected $storeRepository;
    protected $productAction;
    protected $payPalPayout;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PaypalPayout $payPalPayout,
        ProductAction $productAction,
        StoreRepository $storeRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->payPalPayout = $payPalPayout;
        $this->productAction = $productAction;
        $this->storeRepository = $storeRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()) {
            $params = $this->getRequest()->getParams();
            $this->_setPageData();
            try {
                $emailSubject = "You have a payout!";
                $note = "Test";
                $paidData = $this->payPalPayout->executePaid(
                    $params['receiver_email'],
                    $params['campaign_profit'],
                    $params['campaign_id'],
                    $emailSubject,
                    $note
                );

                // Save $payoutBatchId to campaign
                try {
                    $this->updateCampaignPaymentStatus(
                        $params['campaign_id'],
                        $paidData['paymentStatus']
                    );
                    $this->updateCampaignPayoutBatchId(
                        $params['campaign_id'],
                        $paidData['payoutBatchId']
                    );
                } catch (Exception $e) {
                    $this->messageManager->addError(__('Something went wrong. Cannot paid right now.'));
                }
            } catch (PayPalConnectionException $e) {
                $this->messageManager->addError(__('Something went wrong. Cannot paid right now.'));
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    public function getResultPage()
    {
        if ($this->resultPage === null) {
            $resultPage = $this->resultPageFactory->create();
        }
        else {
            $resultPage = $this->resultPage;
        }
        return $resultPage;
    }

    protected function _setPageData()
    {
        $this->resultPage = $this->getResultPage();
        return $this;
    }
    
    protected function updateCampaignPaymentStatus($productId, $status)
    {
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
                // TODO: handle exception
            }
        }
    }
    
    protected function updateCampaignPayoutBatchId($productId, $id)
    {
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            try {
                $this->productAction->updateAttributes(
                    [$productId],
                    ['campaign_payout_batch_id' => $id],
                    $storeId
                );
            } catch (Exception $e) {
                // TODO: handle exception
            }
        }
    }
}
