<?php

namespace Zehntech\Reorder\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Resource;

class ReorderProducts extends \Magento\Catalog\Model\Layer
{
    public function getProductCollection()
    {
        $reorderCollection = $this->getReorderCollection(); 
        if (isset($this->_productCollections['reorder'])) {
            $collection = $this->_productCollections;
        } else {
            $collection = $reorderCollection;
            $this->prepareProductCollection($collection);
            $this->_productCollections['reorder'] = $collection;
        }
        return $collection;
    }

    public function getReorderCollection()
    {
        $reorderCollection = $this->getData('reorder_collection');
        if ($reorderCollection === null) {
            $reorderCollection = $this->registry->registry('reorder_collection');
            if ($reorderCollection) {
                $this->setData('reorder_collection', $reorderCollection);
            }
        }
        return $reorderCollection;
    }
}