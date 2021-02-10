<?php

namespace Zehntech\FmePostEvent\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager)
    {

        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->_messageManager = $messageManager;
    }

    public function execute()
    {
        $page = $this->_pageFactory->create();
        $page->getConfig()->getTitle()->set('Events');

        return $page;

    }

}
