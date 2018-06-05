<?php

namespace Interactone\Crowdfund\Controller\Paypal;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\Session\Proxy as Session;

class Index extends Action
{
    protected $pageFactory;
    protected $session;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Session $session
    ) {
        $this->pageFactory = $pageFactory;
        $this->session = $session;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Paypal Customer'));
            return $resultPage;
        } else {
            $this->_redirect('customer/account/login/');
        }
    }
}
