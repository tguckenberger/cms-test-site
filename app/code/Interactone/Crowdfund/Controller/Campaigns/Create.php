<?php

namespace Interactone\Crowdfund\Controller\Campaigns;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Interactone\Crowdfund\Model\CampaignFactory;
use \Magento\Framework\Message\ManagerInterface as MessageManager;
use \Magento\Customer\Model\Session\Proxy as Session;
use \Magento\Catalog\Api\Data\ProductInterfaceFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Interactone\Crowdfund\Helper\Config;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use \Magento\MediaStorage\Model\File\UploaderFactory;
use \Magento\Framework\Filesystem;
use \Magento\Catalog\Model\Product;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Zend_Filter_Input;
use \Magento\Framework\App\Action\Action;
use \Magento\Catalog\Model\Product\Attribute\Source\Status;
use \Interactone\Crowdfund\Model\Config\Source\Status as CampaignStatus;
use \Exception;

class Create extends Action
{
    protected $campaignFactory;
    protected $pageFactory;
    protected $messageManager;
    protected $session;
    protected $product;
    protected $zendFilterInput;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var $helperConfig
     * */
    protected $helperConfig;

    /**
     * @var $date
     * */
    protected $date;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     * */
    protected $fileUploaderFactory;

    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    protected $filesystem;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        CampaignFactory $campaignFactory,
        MessageManager $messageManager,
        Session $session,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        Config $helperConfig,
        DateTime $date,
        DateFilter $dateFilter,
        UploaderFactory $fileUploaderFactory,
        Filesystem $_filesystem,
        Product $product,
        Zend_Filter_Input $zendFilterInput
    ) {
        $this->campaignFactory = $campaignFactory;
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
        $this->session = $session;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->helperConfig = $helperConfig;
        $this->date = $date;
        $this->dateFilter = $dateFilter;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->filesystem = $_filesystem;
        $this->product = $product;
        $this->zendFilterInput = $zendFilterInput;
        return parent::__construct($context);
    }

    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_idPrototype = $this->getRequest()->getPost('product_prototypes_id');
                if ($_idPrototype == null) {
                    $this->messageManager->addErrorMessage(__("Please select a product"));
                    $this->_redirect('crowdfund/campaigns/create/');
                } else {
                    try {
                        $productPrototype = $this->productRepository->getById($_idPrototype);
                    } catch (NoSuchEntityException $exception) {
                        $this->messageManager->addErrorMessage($exception->getMessage());
                        $this->_redirect('crowdfund/campaigns/create/');
                    }
                }

                $product = $this->productFactory->create();
                $campaignEndDate = date(
                    'm-d-Y',
                    strtotime($this->date->gmtDate() .' +'.(int)$this->getRequest()->getPost('length').' day')
                );
                $data = [
                    'name' => strip_tags($this->getRequest()->getPost('name')),
                    'status' => Status::STATUS_ENABLED,
                    'type_id' => 'designer_product',
                    'attribute_set_id' => $this->helperConfig->getConfigAttributeSetId(),
                    'sku' => 'campaign-'.$this->helperConfig->generateRandomString(5),
                    'website_ids' => [1],
                    'visibility' => 4,
                    'description' => $this->getRequest()->getPost('description'),
                    'campaign_goals' => $this->getRequest()->getPost('goal'),
                    'price' => $this->getRequest()->getPost('price'),
                    'campaign_profit' => $this->getRequest()->getPost('profit'),
                    'campaign_customer_id' => $this->session->getCustomer()->getId(),
                    'campaign_status' => CampaignStatus::STATUS_PROCESSING,
                    'campaign_end_date' => $campaignEndDate,
                    'campaign_create_from' => ($productPrototype) ? $productPrototype->getSku() : '',
                    'category_ids' => $this->helperConfig->getConfigCampaignCategoryId(),
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'manage_stock' => 1,
                        'is_in_stock' => 1,
                        'qty' => $this->getRequest()->getPost('goal')
                    ]
                ];
                $inputFilter = new Zend_Filter_Input(
                    ['campaign_end_date' => $this->dateFilter],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $imagePath = $this->uploadImage('image');

                $product->setData($data);
                // Save product image
                try {
                    // Add Images To The Product
                    if ($imagePath) {
                        // Save Product type design_product ,attribute set 'campaign'
                        $campaign_product = $this->productRepository->save($product);
                        $_cp_product = $this->product->load($campaign_product->getId());
                        $_cp_product->addImageToMediaGallery(
                            $imagePath,
                            ['image', 'small_image', 'thumbnail'],
                            false,
                            false
                        );
                        $_cp_product->save();
                        $this->messageManager->addSuccessMessage(__("Campaign Product Created"));
                    }
                } catch (Exception $e) {
                    $this->messageManager->addException($e, __('We can\'t save the Campaign Product.'));
                }
                $this->_redirect('crowdfund/campaigns/view/');
            } else {
                $resultPage = $this->pageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Create Campaign Products'));
                return $resultPage;
            };
        } else {
            $this->_redirect('customer/account/login/');
        }
    }

    protected function uploadImage($fileID)
    {
        $uploader = $this->fileUploaderFactory->create(['fileId' => $fileID]);

        try {
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $image_path = null;

            if ($uploader->getFileSize() > $this->getMaxConfigSize()) {
                throw new LocalizedException(
                    __('File Upload max size: %1 byte', $this->getMaxConfigSize())
                );
            }
            $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('crowdfund/images');

            $result = $uploader->save($path);
            if (!$result) {
                throw new LocalizedException(
                    __('File cannot be saved to path: %1', $path)
                );
            } else {
                $image_path = $path . '/' . $result['file'];
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return false;
        }
        return $image_path;
    }

    private function getAllowedExtensions()
    {
        return explode(',', $this->helperConfig->getConfigImageTypes());
    }

    private function getMaxConfigSize()
    {
        return $this->helperConfig->getConfigImageSize() * 1024;
    }
}
