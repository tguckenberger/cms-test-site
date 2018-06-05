<?php

namespace Interactone\Crowdfund\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => 'Sandbox'],
            ['value' => 1, 'label' => 'Live']
        ];
    }
}