<?php

namespace Zehntech\Reorder\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
	protected function _getProductCollection()
	{
	    if ($this->_productCollection === null) {
	        $layer = $this->getLayer();
	        $reorderCollection = $this->_coreRegistry->registry('reorder_collection');
	        if ($reorderCollection) {
	            $layer->setReorderCollection($reorderCollection);
	        }
	        $collection = $layer->getProductCollection();
	        $this->_productCollection = $collection;
	    }
	    return $this->_productCollection;
	}


        
}