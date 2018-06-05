<?php

namespace Interactone\Crowdfund\Model\ResourceModel\Campaign;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'campaign_id';
    protected $_eventPrefix = 'interactone_crowdfund_campaign_collection';
    protected $_eventObject = 'campaign_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Interactone\Crowdfund\Model\Campaign',
            'Interactone\Crowdfund\Model\ResourceModel\Campaign'
        );
    }
}