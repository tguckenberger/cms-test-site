<?php

namespace Interactone\Crowdfund\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;

class Campaign extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'interactone_crowdfund_campaign';

    protected $cacheTag = 'interactone_crowdfund_campaign';
    protected $eventPrefix = 'interactone_crowdfund_campaign';

    protected function _construct()
    {
        $this->_init('Interactone\Crowdfund\Model\ResourceModel\Campaign');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
