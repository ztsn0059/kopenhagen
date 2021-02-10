<?php

/**
 * @package   Zehntech_PriceMatrix
 * @author    Zehntech
 */

namespace Zehntech\PriceMatrix\Controller\Adminhtml\Matrix;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Zehntech\PriceMatrix\Model\SmallMatrixFactory
     */
    var $gridFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Zehntech\PriceMatrix\Model\SmallMatrixFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Zehntech\PriceMatrix\Model\SmallMatrixFactory $gridFactory
    )
    {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('smallpricematrix/matrix/addrow');
            return;
        }
        try {
            $rowData = $this->gridFactory->create();
            $rowData->setData($data);
            if (isset($data['id'])) {
                $rowData->setEntityId($data['id']);
            }
            $rowData->save();
            $this->messageManager->addSuccess(__('Row data has been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('smallpricematrix/matrix/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_PriceMatrix::save');
    }
}