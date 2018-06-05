<?php

namespace Interactone\Crowdfund\Controller\Paypal;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Data\Form\FormKey\Validator;
use \Magento\Customer\Model\Session\Proxy as Session;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Controller\ResultFactory;
use \Exception;

class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultRedirectFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Session\Proxy $session
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\ResultFactory $resultRedirectFactory
     * */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Validator $formKeyValidator,
        Session $session,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ManagerInterface $messageManager,
        ResultFactory $resultRedirectFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->session = $session;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Process save paypal customer
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $redirectUrl = null;
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $paypal_account = $this->getRequest()->getPostValue("paypal-account");

        if ($this->session->isLoggedIn()) {
            $customerId = $this->session->getCustomer()->getId();
            $customer = $this->customerRepositoryInterface->getById($customerId);
            $customer->setCustomAttribute('paypal_account', $paypal_account);

            try {
                $this->customerRepositoryInterface->save($customer);
                $this->messageManager->addSuccess(__('You saved the paypal account.'));
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the paypal account.'));
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
