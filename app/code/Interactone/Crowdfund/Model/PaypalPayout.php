<?php

namespace Interactone\Crowdfund\Model;

use \PayPal\Rest\ApiContext;
use \PayPal\Auth\OAuthTokenCredential;
use \PayPal\Api\Payout;
use \PayPal\Api\PayoutSenderBatchHeader;
use \PayPal\Api\PayoutItem;
use \PayPal\Api\Currency;
use \Interactone\Crowdfund\Helper\Data;

class PaypalPayout
{
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    private function _getAPIContext()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->helper->getClientIdConfig(),
                $this->helper->getSecretConfig()
            )
        );

        return $apiContext;
    }

    public function executePaid($receiverEmail, $profit, $campaignId, $emailSubject, $note)
    {
        $payouts = new Payout();
        $senderBatchHeader = new PayoutSenderBatchHeader();
        $senderBatchHeader->setSenderBatchId(uniqid())
            ->setEmailSubject($emailSubject);
        $senderItem = new PayoutItem();
        $senderItem->setRecipientType('Email')
            ->setNote($note)
            ->setReceiver($receiverEmail)
            ->setSenderItemId($campaignId)
            ->setAmount(
                new Currency(
                    '{
                        "value": '. $profit .',
                        "currency":"USD"
                    }'
                )
            );
        $payouts->setSenderBatchHeader($senderBatchHeader)
            ->addItem($senderItem);
        $apiContext = $this->_getAPIContext();
        $params = array('sync_mode' => 'false');
        $response = $payouts->create($params, $apiContext);

        if (empty(!$response)) {
            $payoutBatchHeader = $response->getBatchHeader();
            $payoutBatchId = $payoutBatchHeader->getPayoutBatchId();
            $paymentStatus = $payoutBatchHeader->getBatchStatus();

            return [
                'payoutBatchId' => $payoutBatchId,
                'paymentStatus' => $paymentStatus
            ];
        }

        return [];
    }

    public function getPayoutStatus($payoutBatchId)
    {
        $payouts = new Payout();
        $apiContext = $this->_getAPIContext();
        $response = $payouts->get($payoutBatchId, $apiContext);
        $payoutBatchHeader = $response->getBatchHeader();

        return $payoutBatchHeader->getBatchStatus();
    }
}