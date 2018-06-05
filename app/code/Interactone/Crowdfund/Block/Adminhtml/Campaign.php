<?php

namespace Interactone\Crowdfund\Block\Adminhtml;

use \Magento\Backend\Block\Widget\Context;
use \Magento\Catalog\Model\Product\TypeFactory;
use \Magento\Catalog\Model\ProductFactory;

class Campaign extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        TypeFactory $typeFactory,
        ProductFactory $productFactory,
        $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->typeFactory = $typeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add Product Campaign'),
            'class' => 'add primary',
            'button_class' => 'primary',
            'onclick' => "setLocation('" . $this->_getProductCreateUrl('designer_product') . "')",
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param string $type
     * @return string
     */
    protected function _getProductCreateUrl($type)
    {
        return $this->getUrl(
            'catalog/product/new',
            ['set' => $this->productFactory->create()->getDefaultAttributeSetId(), 'type' => $type]
        );
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}