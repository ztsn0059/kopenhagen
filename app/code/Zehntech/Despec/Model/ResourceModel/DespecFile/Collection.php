<?php

/**
 * Zehntech Mmd Interface. *
 * @category    Zehntech *
 * @author  @SumitKumarNamdeo
 */


namespace Zehntech\Despec\Model\ResourceModel\DespecFile;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */

    protected $_idFieldName = 'id';

    /**
     * Define resource model.
     */

    protected function _construct()
    {
        $this->_init('Zehntech\Despec\Model\DespecFile', 'Zehntech\Despec\Model\ResourceModel\DespecFile');
    }
}