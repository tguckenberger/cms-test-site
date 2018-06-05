<?php

namespace Interactone\Crowdfund\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const PATH_CROWDFUNDED_GENERAL = 'crowdfunded/general/';
    const PATH_CAMPAIGN_CATEGORY_ID = 'crowdfunded/general/campaign_category_id';
    const PATH_PROTOTYPES_CATEGORY_ID = 'crowdfunded/general/prototype_parent_category_id';
    const PATH_PRODUCT_ATTRIBUTE_SET = 'crowdfunded/general/product_attribute_set';

    protected $scopeConfig;

    /**
     * __construct Config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     **/
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigCampaignCategoryId()
    {
        return $this->getConfig(self::PATH_CAMPAIGN_CATEGORY_ID);
    }

    public function getConfigPrototypesCategoryId()
    {
        return $this->getConfig(self::PATH_PROTOTYPES_CATEGORY_ID);
    }

    public function getConfigAttributeSetId()
    {
        return $this->getConfig(self::PATH_PRODUCT_ATTRIBUTE_SET);
    }

    public function getConfigCrowdufundedGeneral($field)
    {
        return $this->getConfig(self::PATH_CROWDFUNDED_GENERAL.$field);
    }

    protected function getEmailConfig($key)
    {
        return $this->getConfig('crowdfunded/email/' . $key);
    }

    public function getConfigSenderEmailIdentity()
    {
        return $this->getEmailConfig('sender_email_identity');
    }

    public function getConfigEmailSuccess()
    {
        return $this->getEmailConfig('success');
    }

    public function getConfigEmailFail()
    {
        return $this->getEmailConfig('failure');
    }
    public function getConfigEmailPaid()
    {
        return $this->getEmailConfig('paid');
    }

    public function getConfigImageTypes()
    {
        return $this->getConfig('crowdfunded/image/file_types');
    }

    public function getConfigImageSize()
    {
        return $this->getConfig('crowdfunded/image/file_size');
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
