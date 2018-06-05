<?php

namespace Interactone\Crowdfund\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Customer\Api\CustomerRepositoryInterface;
use \Magento\Framework\UrlInterface;
use \Exception;

/**
 * Class Author.
 */
class Author extends Column
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        UrlInterface $urlBuilder,
        $components = [],
        $data = []
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     * @throws Exception
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $author_id = $item[$this->getData('name')];
                try {
                    $customer = $this->customerRepositoryInterface->getById($author_id);
                } catch (Exception $e) {
                    //TODO: handle exception
                }
                $item[$this->getData('name')] = $customer->getFirstName() . ' ' . $customer->getLastName();
            }
        }

        return $dataSource;
    }

    protected function prepareItem(array $item)
    {
        $storeId = $this->context->getFilterParam('store_id');
        $author_id = $item[$this->getData('name')];

        if (empty($author_id)) {
            return '';
        }
        try {
            $customer = $this->customerRepositoryInterface->getById($author_id);
        } catch (Exception $e) {
            //TODO: handle exception
        }

        return [
            'href' => $this->urlBuilder->getUrl(
                'customer/index/edit',
                ['id' => $author_id, 'store' => $storeId]
            ),
            'label' => $customer->getFirstName() . ' ' . $customer->getLastName(),
            'hidden' => false,
        ];
    }
}
