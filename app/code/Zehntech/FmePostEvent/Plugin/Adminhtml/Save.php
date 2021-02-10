<?php

/**
 * after event save
 */

namespace Zehntech\FmePostEvent\Plugin\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Zehntech\FmePostEvent\Helper\ApproveEventMail;

/**
 *
 */
class Save
{

    protected $_resultRedirect;
    protected $_approveEventMailHelper;


    function __construct(ResultFactory $result, RequestInterface $request, ApproveEventMail $approveEventMailHelper)
    {
        $this->_resultRedirect = $result;
        $this->_request = $request;
        $this->_approveEventMailHelper = $approveEventMailHelper;
    }


    public function afterExecute()
    {

        $resultRedirect = $this->_resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $contactEmail = $this->_request->getParam('contact_email');
        $contactName = $this->_request->getParam('contact_name');
        $eventName = $this->_request->getParam('event_name');


        if ($this->_request->getParam('is_active') == 1 && !empty($contactEmail) && !empty($contactName)) {

            $this->_approveEventMailHelper->notify($contactName, $contactEmail, $eventName);
        }

        return $resultRedirect->setPath('*/*/');

    }

}