<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Zehntech\CustomerProducts\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CustomerCheck extends Template
{
    private $_customerSession;

    public function __construct(
        Context $context,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
    }

    public function isLoggedIn()
    {
        if(!$this->_customerSession->isLoggedIn())
            return true;
        else 
            return false;
    }

    public function helloWorld()
    {
        print_r("hello magento");
        die("try");
    }


}
