<?php
namespace Zehntech\Remmer\Controller\Adminhtml\Fileupload;

class MassDelete extends \Magento\Backend\App\Action {

    protected $_filter;

    protected $_collectionFactory;

    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Zehntech\Remmer\Model\ResourceModel\RemmerFile\CollectionFactory $collectionFactory,
        \Zehntech\Remmer\Model\RemmerFile $remmer,
        \Magento\Backend\App\Action\Context $context
        ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->remmer = $remmer;
        parent::__construct($context);
    }

    public function execute() {
        try{ 

            $logCollection = $this->_filter->getCollection($this->_collectionFactory->create());
            //echo "<pre>";
            //print_r($logCollection->getData());
            //exit;

            foreach ($logCollection as $item) {
                $item_s = $this->remmer->load($item->getId());
                // print_r($item_s->getId());
                $item_s->delete();
            }
            // die("hello");
            $this->messageManager->addSuccess(__('File record deleted Successfully.'));
        }catch(Exception $e){
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('remmerfile/fileupload/index'); //Redirect Path
    }

     /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Zehntech_Remmer::view');
    }
}