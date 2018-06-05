<?php

namespace Interactone\Crowdfund\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    const STATUS_SUCCESS = 'success';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILURE = 'failure';
    const STATUS_PAID = 'paid';

    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_PROCESSING, 'label' => 'Processing'],
            ['value' => self::STATUS_SUCCESS, 'label' => 'Success'],
            ['value' => self::STATUS_FAILURE, 'label' => 'Failure'],
            ['value' => self::STATUS_PAID, 'label' => 'Paid']
        ];
    }
}