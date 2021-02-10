<?php

/**
 * Zehntech ResourceModel.
 * @category    Zehntech
 * @author      Zehntech Technologies Private Limited
 */

namespace Zehntech\PriceMatrix\Model\ResourceModel\MediumMatrix;

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
        $this->_init('Zehntech\PriceMatrix\Model\MediumMatrix', 'Zehntech\PriceMatrix\Model\ResourceModel\MediumMatrix');
    }
}