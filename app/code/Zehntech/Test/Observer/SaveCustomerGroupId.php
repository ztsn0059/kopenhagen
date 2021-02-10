<?php

namespace Zehntech\Test\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

Class SaveCustomerGroupId implements ObserverInterface 
{
    protected $_customerRepositoryInterface;
    protected $_request;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
          $postData = $this->_request->getPost();
        if($postData["group_id"] == 2) {
            $customer->setGroupId($postData["group_id"]);
            $this->_customerRepositoryInterface->save($customer);;
        }
    }
}