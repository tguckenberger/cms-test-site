<?php

namespace Interactone\Crowdfund\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Encryption\EncryptorInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\Pricing\Helper\Data as HelperPrice;
use \Magento\CatalogInventory\Api\StockRegistryInterface;
use \Magento\Framework\Stdlib\DateTime;
use \Magento\Store\Model\ScopeInterface;
use \Exception;
use \Magento\Framework\Exception\MailException;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    protected $scopeConfig;

    protected $encryptor;

    /**
     * @var \Interactone\Crowdfund\Helper\Config
     * */
    protected $config;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    protected $helperPrice;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockRegistry;

    /**
     * __construct Config
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Interactone\Crowdfund\Helper\Config $config
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\Pricing\Helper\Data $helperPrice
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Config $config,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepositoryInterface,
        HelperPrice $helperPrice,
        StockRegistryInterface $stockRegistry
    ) {
        $this->timezoneInterface = $timezoneInterface;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->helperPrice = $helperPrice;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param string $dateTime
     * @return string $dateTime as time zone
     */
    public function getTimeAccordingToTimeZone($dateTime)
    {
        $convert_date = (new \DateTime())->setTimestamp(strtotime($dateTime));
        $d = $convert_date->format(DateTime::DATETIME_PHP_FORMAT);

        $dateCampaign = (new \DateTime(trim($d)))->format('F j, Y h:i:s A');
        return $dateCampaign;
    }

    protected function _getConfig($key)
    {
        return $this->scopeConfig->getValue(
            'crowdfunded/paypal/' . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getClientIdConfig()
    {
        return $this->encryptor->decrypt($this->_getConfig('client_id'));
    }

    public function getSecretConfig()
    {
        return $this->encryptor->decrypt($this->_getConfig('secret'));
    }

    public function getModeConfig()
    {
        return $this->_getConfig('mode');
    }

    protected function _sendCampaignMail($template, $dataProduct)
    {
        $store = $this->storeManager->getStore()->getId();
        $senderEmailIdentity = ($this->config->getConfigSenderEmailIdentity()) ? $this->config->getConfigSenderEmailIdentity() : 'general';
        try {
            $customer = $this->customerRepositoryInterface->getById($dataProduct->getCampaignCustomerId());
        } catch (Exception $e) {
            //TODO: handle exception
        }

        $stockQty = $this->stockRegistry->getStockItem($dataProduct->getId())->getQty();
        $sold = (int) $dataProduct->getCampaignGoals() - (int) $stockQty;
        $goal = $sold . '/' . $dataProduct->getCampaignGoals();

        $transport = $this->transportBuilder->setTemplateIdentifier($template)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars(
                [
                    'store' => $this->storeManager->getStore(),
                    'customer_name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
                    'campaign_goal' => $goal,
                    'campaign_end_date' => $this->getTimeAccordingToTimeZone($dataProduct->getCampaignEndDate()),
                    'price' => $this->helperPrice->currency($dataProduct->getPrice(), true, false),
                    'campaign_profit' => $this->helperPrice->currency($dataProduct->getCampaignProfit(), true, false),
                    'product' => $dataProduct,
                    'customer' => $customer
                ]
            )
            ->setFrom($senderEmailIdentity)
            ->addTo($customer->getEmail(), $customer->getFirstName() . ' ' . $customer->getLastName())
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (MailException $e) {
           //TODO: handle exception
        }
        return $this;
    }

    public function sendCampaignSuccessEmail($data)
    {
        $this->_sendCampaignMail($this->config->getConfigEmailSuccess(), $data);
        return $this;
    }

    public function sendCampaignFailEmail($data)
    {
        $this->_sendCampaignMail($this->config->getConfigEmailFail(), $data);
        return $this;
    }

    public function sendCampaignPaidEmail($data)
    {
        $this->_sendCampaignMail($this->config->getConfigEmailPaid(), $data);
        return $this;
    }
}
