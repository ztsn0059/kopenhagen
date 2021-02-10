<?php
/**
 * Zehntech_PriceMatrix Controller
 * @package   Zehntech_PriceMatrix
 * @author    Zehntech
 */

namespace Zehntech\PriceMatrix\Controller\Adminhtml\MediumMatrix;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Zehntech\PriceMatrix\Model\ResourceModel\MediumMatrix\CollectionFactory;
use Zehntech\PriceMatrix\Model\MediumMatrix;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter.​_
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MediumMatrix $mediumMatrix
    )
    {

        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->mediumMatrix = $mediumMatrix;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $recordDeleted = 0;
        foreach ($collection->getItems() as $record) {
            $_record = $this->mediumMatrix->load($record->getId());
            $_record->delete();
            $recordDeleted++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $recordDeleted));

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Check Category Map recode delete Permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_PriceMatrix::row_data_delete');
    }
}