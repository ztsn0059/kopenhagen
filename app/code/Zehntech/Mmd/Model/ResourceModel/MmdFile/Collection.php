<?php

/**
 * Zehntech Mmd Interface. *
 * @category    Zehntech *
 * @author  @SumitKumarNamdeo
 */


namespace Zehntech\Mmd\Model\ResourceModel\MmdFile;

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
        $this->_init('Zehntech\Mmd\Model\MmdFile', 'Zehntech\Mmd\Model\ResourceModel\MmdFile');
    }
}