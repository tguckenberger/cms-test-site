<?php

namespace Interactone\Crowdfund\Model;

use \Magento\Payment\Model\Method\AbstractMethod;

/**
 * Pay In Store payment method model
 */
class PaymentMethod extends AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'adaptive_paypal';
}