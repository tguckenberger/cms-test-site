<?php

namespace Interactone\Crowdfund\Setup;

use \Magento\Framework\Setup\InstallDataInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;
use \Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use \Magento\Catalog\Setup\CategorySetupFactory;
use \Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use \Magento\Eav\Setup\EavSetup;
use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Eav\Model\Config;
use \Magento\Customer\Setup\CustomerSetupFactory;
use \Magento\Customer\Model\Customer;
use \Magento\Sales\Setup\SalesSetupFactory;
use \Magento\Quote\Setup\QuoteSetupFactory;
use \Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use \Magento\Framework\DB\Ddl\Table;
use \Exception;

class InstallData implements InstallDataInterface
{
    private $attributeSetFactory;
    private $categorySetupFactory;
    private $eavSetupFactory;
    private $attributeSet;
    private $eavConfig;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetup
     */
    private $salesSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSet $attributeSet,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSet = $attributeSet;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Create product type design product
        $attributes = [
            'cost',
            'price',
            'weight',
            'tax_class_id'
        ];

        foreach ($attributes as $attributeCode) {
            $relatedProductTypes = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'apply_to')
            );
            if (!in_array('designerProductType', $relatedProductTypes)) {
                $relatedProductTypes[] = 'designer_product';
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $attributeCode,
                    'apply_to',
                    implode(',', $relatedProductTypes)
                );
            }
        }

        // Setup data product attribute set name Campaign
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $attributeSet = $this->attributeSetFactory->create();
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        $data = [
            'attribute_set_name' => 'Campaign',
            'entity_type_id' => $entityTypeId,
            'sort_order' => 300,
        ];
        $attributeSet->setData($data);
        try {
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($attributeSetId)->save();
        } catch (Exception $e) {
            //TODO: handle exception
        }

        // Setup new attribute product
        $attributesDesignerProduct = [
            [
                'name' => 'campaign_end_date',
                'label' => 'Campaign End Date',
                'type' => 'datetime',
                'inputType' => 'date',
                'group' => true
            ],
            [
                'name' => 'campaign_goals',
                'label' => 'Goals',
                'type' => 'text',
                'inputType' => 'text',
                'group' => true
            ],
            [
                'name' => 'campaign_profit',
                'label' => 'Profit',
                'type' => 'varchar',
                'inputType' => 'text',
                'group' => true
            ],
            [
                'name' => 'campaign_customer_id',
                'label' => 'Customer Id',
                'type' => 'int',
                'inputType' => 'text',
                'group' => true
            ],
            [
                'name' => 'campaign_status',
                'label' => 'Campaign Status',
                'type' => 'varchar',
                'inputType' => 'text',
                'group' => true
            ],
            [
                'name' => 'campaign_payment_status',
                'label' => 'Campaign Payment Status',
                'type' => 'varchar',
                'inputType' => 'text',
                'group' => false
            ],
            [
                'name' => 'campaign_payout_batch_id',
                'label' => 'Campaign Payout Batch Id',
                'type' => 'varchar',
                'inputType' => 'text',
                'group' => false
            ],
            [
                'name' => 'campaign_create_from',
                'label' => 'Campaign Create From',
                'type' => 'varchar',
                'inputType' => 'text',
                'group' => false
            ]
        ];
        foreach ($attributesDesignerProduct as $attribute) {
            $eavSetup->removeAttribute(Product::ENTITY, $attribute['name']);
            $options = [
                'type' => $attribute['type'],
                'attribute_set' => 'Campaign',
                'attribute_set_name' => 'Campaign',
                'backend' => '',
                'frontend' => '',
                'label' => $attribute['label'],
                'input' => $attribute['inputType'],
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'designer_product'
            ];
            if ($attribute['group']) {
                $options['group'] = 'Campaign';
            }

            $eavSetup->addAttribute(
                Product::ENTITY,
                $attribute['name'],
                $options
            );
        }

        // Create attribute customer
        $eavSetup->removeAttribute(Product::ENTITY, 'paypal_account');
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /**
         * @var $attributeSet AttributeSet
         */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'paypal_account', [
            'type' => 'varchar',
            'label' => 'PayPal Account',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
        ]);
        // Add attribute to attribute set
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'paypal_account')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);

        $attribute->save();

        // Create Attribute Quote and Order Item

        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $attributeOptions = [
            'type'     => Table::TYPE_TEXT,
            'visible'  => true,
            'required' => false
        ];
        $quoteSetup->addAttribute('quote_item', 'image_product_designer', $attributeOptions);
        $salesSetup->addAttribute('order_item', 'image_product_designer', $attributeOptions);

        $setup->endSetup();
    }
}
