<?php
namespace Interactone\Crowdfund\Controller\Campaigns;

class Test extends \Magento\Framework\App\Action\Action
{
    protected $productFactory;
    protected $session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\Session $session
    )
    {
        $this->productFactory = $productFactory;
        $this->session = $session;
        return parent::__construct($context);
    }

    public function execute()
    {
        $product = $this->productFactory->create();
        $product->setSku('yeet-123');
        $product->setName('Simple Product');
        $product->setAttributeSetId(4);
        $product->setStatus(1); // Status on product enabled/ disabled 1/0
        $product->setWeight(10);
        $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
        $product->setTaxClassId(0); // Tax class id
        $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
        $product->setPrice(100); // price of product
        $product->setStockData(
            array(
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'is_in_stock' => 1,
                'qty' => 9999
            )
        );
        $product->save();
    }
}