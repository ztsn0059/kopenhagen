<?php

/**
 * Copyright © 2019 Zehntech. All rights reserved.
 * See COPYING.txt for license details.
 * zehntech.com
 */

namespace Zehntech\OfflinePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    protected $_session;

    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    /*
    * disabling offline payment for all
    * if customer set to 'Pay from invoice' enable it.
    */

    public function execute(Observer $observer)
    {
        if ($observer->getEvent()->getMethodInstance()->getCode() == "offlinepayment") {

            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);

            if ($this->_session->isLoggedIn() && $this->_session->getCustomerData()->getCustomAttribute('pay_from_invoice') && ($this->_session->getCustomerData()->getCustomAttribute('pay_from_invoice')->getValue() == true)) {

                $checkResult->setData('is_available', true);
            }

        }


    }
}