<?php
namespace Interactone\Crowdfund\Ui\Component\Listing\Column;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use \Interactone\Crowdfund\Model\Config\Source\Status as CampaignStatus;
use \Exception;

/**
 * Class PageActions
 */
class CampaignActions extends Column
{
    /** Url path */
    const CAMPAIGN_URL_PATH_EDIT = 'catalog/product/edit';
    const CAMPAIGN_URL_PATH_DELETE = 'catalog/product/delete';
    const CAMPAIGN_URL_PATH_PAID = 'crowdfund/campaigns/paid';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @var Escaper
     */
    private $escaper;
    
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $components
     * @param array $data
     * @param string $editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlBuilder $actionUrlBuilder,
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = [],
        $editUrl = self::CAMPAIGN_URL_PATH_EDIT
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
        $this->editUrl = $editUrl;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl($this->editUrl, ['id' => $item['entity_id']]),
                        'label' => __('Edit')
                    ];
                    $title = $this->getEscaper()->escapeHtml($this->productRepository->getById($item['entity_id'])->getName());
                    $nameCustomer = $this->getEscaper()->escapeHtml($item['campaign_customer_id']);
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::CAMPAIGN_URL_PATH_DELETE, ['id' => $item['entity_id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title)
                        ]
                    ];
                    if ($item['campaign_status'] === CampaignStatus::STATUS_SUCCESS &&
                        !isset($item['campaign_payment_status'])
                    ) {
                        try {
                            $receiverEmail = $this->customerRepository->getById(
                                $this->productRepository->getById($item['entity_id'])->getCampaignCustomerId()
                            )->getEmail();
                            $idCampaign = $this->productRepository->getById($item['entity_id'])->getId();
                            $profitCampaign = $this->productRepository->getById($item['entity_id'])->getCampaignProfit();
                        } catch (Exception $e) {
                            //TODO: handle exception
                        }
                        if ($idCampaign) {
                            $item[$name]['paid'] = [
                                'href' => $this->urlBuilder->getUrl(
                                    self::CAMPAIGN_URL_PATH_PAID,
                                    [
                                        'receiver_email' => $receiverEmail,
                                        'campaign_profit' => $profitCampaign,
                                        'campaign_id' => $idCampaign
                                    ]
                                ),
                                'label' => __('Paid'),
                                'confirm' => [
                                    'title' => __('Paid %1', $title),
                                    'message' => __('Are you sure you want to paid for %1?', $nameCustomer)
                                ]
                            ];
                        }
                    }
                }
                if (isset($item['identifier'])) {
                    $item[$name]['preview'] = [
                        'href' => $this->actionUrlBuilder->getUrl(
                            $item['identifier'],
                            isset($item['_first_store_id']) ? $item['_first_store_id'] : null,
                            isset($item['store_code']) ? $item['store_code'] : null
                        ),
                        'label' => __('View')
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
