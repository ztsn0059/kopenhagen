<?php

/**
 * @package   Zehntech_PriceMatrix
 * @author    Zehntech
 */

namespace Zehntech\PriceMatrix\Controller\Adminhtml\MediumMatrix;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Zehntech\PriceMatrix\Model\MediumMatrixFactory
     */
    var $gridFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Zehntech\PriceMatrix\Model\MediumMatrixFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Zehntech\PriceMatrix\Model\MediumMatrixFactory $gridFactory
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
            $this->_redirect('mediumpricematrix/mediummatrix/addrow');
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
        $this->_redirect('mediumpricematrix/mediummatrix/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_PriceMatrix::save');
    }
}