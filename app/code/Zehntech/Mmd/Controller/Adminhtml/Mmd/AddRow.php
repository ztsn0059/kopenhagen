<?php
/**
 * @package   Zehntech
 * @author    SumitKumarNamdeo
 */

namespace Zehntech\Mmd\Controller\Adminhtml\Mmd;

use Magento\Framework\Controller\ResultFactory;

class AddRow extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Zehntech\Mmd\Model\MmdFileFactory
     */
    private $gridFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry ,
     * @param \Zehntech\Mmd\Model\MmdFileFactory $gridFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Zehntech\Mmd\Model\MmdFileFactory $gridFactory
    )
    {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->gridFactory = $gridFactory;
    }

    /**
     * Mapped Grid List page.
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $rowId = (int)$this->getRequest()->getParam('id');
        $rowData = $this->gridFactory->create();
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */

        if ($rowId) {

            $rowData = $rowData->load($rowId);
            $rowTitle = $rowData->getTitle();

            if (!$rowData->getEntityId()) {

                $this->messageManager->addError(__('row data no longer exist.'));
                $this->_redirect('mmdfile/mmd/rowdata');
                return;
            }
        }

        $this->coreRegistry->register('row_data', $rowData);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $rowId ? __('Edit Row Data ') . $rowTitle : __('MMD File Upload');
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_Mmd::add_row');
    }
}