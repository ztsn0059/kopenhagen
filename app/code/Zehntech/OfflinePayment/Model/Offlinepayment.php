<?php

/**
 * Copyright © 2019 Zehntech. All rights reserved.
 * See COPYING.txt for license details.
 * zehntech.com
 */

namespace Zehntech\OfflinePayment\Model;

class Offlinepayment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CUSTOM_INVOICE_CODE = 'offlinepayment';
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CUSTOM_INVOICE_CODE;
}