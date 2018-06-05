<?php

namespace Interactone\Crowdfund\Controller\Adminhtml\Campaigns;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $resultPage;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        // Call page factory to render layout and page content
        $this->_setPageData();
        return $this->getResultPage();
    }

    public function getResultPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultPageFactory->create();
        }
        return $this->resultPage;
    }

    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Interactone_Crowdfund::campaigns');
        $resultPage->getConfig()->getTitle()->prepend((__('Campaigns')));

        // Add bread crumb
        $resultPage->addBreadcrumb(__('Interactone'), __('Interactone'));
        $resultPage->addBreadcrumb(__('Crowdfund'), __('Campaigns'));

        return $this;
    }
}
