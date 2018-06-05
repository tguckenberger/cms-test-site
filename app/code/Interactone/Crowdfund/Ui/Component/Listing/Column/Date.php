<?php

namespace Interactone\Crowdfund\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Interactone\Crowdfund\Helper\Data;

/**
 * Class Date.
 */
class Date extends Column
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    protected $helperData;
    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface  $timezone
     * @param Data               $helperData
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        Data $helperData,
        $components = [],
        $data = []
    ) {
        $this->timezone = $timezone;
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    protected function prepareItem(array $item)
    {
        $date = $item[$this->getData('name')];
        if (empty($date)) {
            return '';
        }

        return $this->helperData->getTimeAccordingToTimeZone($date);
    }
}
