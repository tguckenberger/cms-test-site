<?php

namespace Interactone\Crowdfund\Command;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use \Interactone\Crowdfund\Cron\UpdatePaymentStatus;

class UpdateCampaignPaymentStatus extends Command
{
    protected $paymentStatus;

    public function __construct(
        UpdatePaymentStatus $paymentStatus
    ) {
        $this->paymentStatus = $paymentStatus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('interactone:update_campaign_payment_status')->setDescription('Update campaign payment status');
    }

    protected function execute(InputInterface $input, Outputinterface $output)
    {
        $this->paymentStatus->execute();
        return $this->getResponse()->setBody("Done!");
    }
}
