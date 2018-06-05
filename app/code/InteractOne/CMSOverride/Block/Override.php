<?php
namespace InteractOne\CMSOverride\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Cms\Model\PageFactory;

class Override extends Template
{
    protected $cmsPageIdentifier = 'cms-override';
    protected $pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /*
     * Allows user specified cms page to use content
     * of said cms page anywhere. For use
     * with site wide styling.
     */
    public function _toHtml()
    {
        return $this->pageFactory->create()
            ->load($this->cmsPageIdentifier, 'identifier')
            ->getContent();
    }
}
