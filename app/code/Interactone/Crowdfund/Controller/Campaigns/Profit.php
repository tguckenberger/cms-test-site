<?php

namespace Interactone\Crowdfund\Controller\Campaigns;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\Session\Proxy as Session;
use \Interactone\Crowdfund\Helper\Config;
use \Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;

class Profit extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    protected $session;

    /**
     * @var $helperConfig
     * */
    protected $helperConfig;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session,
        Config $helperConfig,
        ResultJsonFactory $resultJsonFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        $this->helperConfig = $helperConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isPost()) {
            $price = $this->getRequest()->getPost('price');
            $goals = $this->getRequest()->getPost('goal');
            $cutForSiteOwner = $this->helperConfig->getConfigCrowdufundedGeneral('cut_for_site_owner');
            if ($price != null || $goals != null) {
                $min_price = $this->helperConfig->getConfigCrowdufundedGeneral('minimum_price');
                $max_price = $this->helperConfig->getConfigCrowdufundedGeneral('maximum_price');
                $min_goal = $this->helperConfig->getConfigCrowdufundedGeneral('minimum_goal');
                $max_goal = $this->helperConfig->getConfigCrowdufundedGeneral('maximum_goal');
                if ($min_price != null && $price < $min_price) {
                    $response = [
                        'error' => true,
                        'error_message' => __('Price should be more than %1', $min_price)
                    ];
                } elseif ($max_price != null && $price > $max_price) {
                    $response = [
                        'error' => true,
                        'error_message' => __('Price should be smaller %1', $max_price)
                    ];
                } elseif ($min_goal != null && $goals < $min_goal) {
                    $response = [
                        'error' => true,
                        'error_message' => __('Profit should be more than %1', $min_goal)
                    ];
                } elseif ($max_goal != null && $goals > $max_goal) {
                    $response = [
                        'error' => true,
                        'error_message' => __('Profit should be smaller %1', $max_goal)
                    ];
                } else {
                    $profit = ($price * $goals) * (1 - ($cutForSiteOwner / 100));
                    $response = [
                        'profit' => $profit,
                        'error' => false,
                        'error_message' => ''
                    ];
                }
            } else {
                $response = [
                    'error' => true,
                    'error_message' => __('Please enter price or goals'),
                ];
            }
            $result->setHttpResponseCode(200);
            $result->setData($response);
            return $result;
        }
    }
    private function setErrorMessage($message, $response = [])
    {
        $response = [
            'error' => true,
            'error_message' => $message
        ];
    }
}
