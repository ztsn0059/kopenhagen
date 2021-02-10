<?php

/**
 * Zehntech Remmer Interface. *
 * @category    Zehntech *
 * @author  @SumitKumarNamdeo
 */


namespace Zehntech\Remmer\Model\ResourceModel\RemmerFile;

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
        $this->_init('Zehntech\Remmer\Model\RemmerFile', 'Zehntech\Remmer\Model\ResourceModel\RemmerFile');
    }
}