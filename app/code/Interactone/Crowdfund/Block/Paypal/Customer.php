<?php

namespace Interactone\Crowdfund\Block\Paypal;

use \Magento\Customer\Model\Session\Proxy as Session;
use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\UrlInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Exception;

class Customer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param \Magento\Customer\Model\Session\Proxy $session
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * */
    public function __construct(
        Session $session,
        Context $context,
        UrlInterface $urlBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->session = $session;
        $this->urlBuilder = $urlBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        return parent::__construct($context);
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl(
            'crowdfund/paypal/save',
            ['_secure' => true]
        );
    }

    public function getPayPalAccount()
    {
        $paypal_customer = null;

        if($this->session->isLoggedIn()){
            $customerId = $this->session->getCustomer()->getId();
            try {
                $customer = $this->customerRepositoryInterface->getById($customerId);
            } catch (Exception $e) {
                //TODO: Handle exception
            }
            $att_paypal_customer = $customer->getCustomAttribute('paypal_account');
            if($att_paypal_customer){
                $paypal_customer = $att_paypal_customer->getValue();
            }
        }
        return $paypal_customer;
    }
}