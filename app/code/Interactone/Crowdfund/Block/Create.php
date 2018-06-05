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

class Create extends Template
{
    /**
     * @var $session
     * */
    protected $session;

    /**
     * @var $productCollectionFactory
     * */
    protected $productCollectionFactory;

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

    public function __construct(
        Session $session,
        Context $context,
        CollectionFactory $productCollectionFactory,
        CategoryFactory $categoryFactory,
        Config $helperConfig,
        Image $helperImageProduct,
        Data $helperPrice
    ) {
        $this->session = $session;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->helperConfig = $helperConfig;
        $this->helperImageProduct = $helperImageProduct;
        $this->helperPrice = $helperImageProduct = $helperPrice;
        return parent::__construct($context);
    }

    public function getProductPrototypes()
    {
        $idCategoryPrototypes = $this->helperConfig->getConfigPrototypesCategoryId();
        $category = $this->categoryFactory->create()->load($idCategoryPrototypes);
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoryFilter($category)
            ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        return $collection;
    }

    public function getImageProduct($product,$type = 'category_page_list',$resize = 100)
    {
        return $this->helperImageProduct
            ->init($product,$type)
            ->constrainOnly(FALSE)
            ->keepAspectRatio(TRUE)
            ->keepFrame(FALSE)
            ->resize($resize)
            ->getUrl();
    }

    public function getPriceProduct($price)
    {
        return $this->helperPrice->currency($price, true, false);
    }

    public function getValidateJsonPrice($required = true)
    {
        $validate = array();
        $validate['required'] = $required;
        if($this->helperConfig->getConfigCrowdufundedGeneral('minimum_price')){
            $validate['min'] = $this->helperConfig->getConfigCrowdufundedGeneral('minimum_price');
        }
        if($this->helperConfig->getConfigCrowdufundedGeneral('maximum_price')) {
            $validate['max'] = $this->helperConfig->getConfigCrowdufundedGeneral('maximum_price');
        }
        return json_encode($validate);
    }


    public function getValidateJsonGoal($required = true)
    {
        $validate = [];
        $validate['required'] = $required;
        if($this->helperConfig->getConfigCrowdufundedGeneral('minimum_goal')) {
            $validate['min'] = $this->helperConfig->getConfigCrowdufundedGeneral('minimum_goal');
        }
        if($this->helperConfig->getConfigCrowdufundedGeneral('maximum_goal')) {
            $validate['max'] = $this->helperConfig->getConfigCrowdufundedGeneral('maximum_goal');
        }
        return json_encode($validate);
    }

    public function getValidateJsonLengthDay($required = true)
    {
        $validate = [];
        $validate['required'] = $required;
        if($this->helperConfig->getConfigCrowdufundedGeneral('minimum_length')) {
            $validate['min'] = $this->helperConfig->getConfigCrowdufundedGeneral('minimum_length');
        }
        if($this->helperConfig->getConfigCrowdufundedGeneral('maximum_length')) {
            $validate['max'] = $this->helperConfig->getConfigCrowdufundedGeneral('maximum_length');
        }
        return json_encode($validate);
    }

    public function getHelperText($field) {
        $html = '<p class="text-helper">';
        if($this->helperConfig->getConfigCrowdufundedGeneral('minimum_'.$field)) {
            $html .= 'Min '.$field.' '.$this->helperConfig->getConfigCrowdufundedGeneral('minimum_'.$field);
            $textMax = ', max ';
        } else {
            $textMax = 'Max ';
        }
        if($this->helperConfig->getConfigCrowdufundedGeneral('maximum_'.$field)) {
            $html .= $textMax.$field.' '.$this->helperConfig->getConfigCrowdufundedGeneral('maximum_'.$field);
        }
        $html .= '</p>';
        return $html;
    }
}
