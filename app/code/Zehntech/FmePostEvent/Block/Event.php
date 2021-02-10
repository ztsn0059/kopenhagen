<?php

namespace Zehntech\FmePostEvent\Block;

class Event extends \FME\Events\Block\Event
{
    public function getFrontEvents()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1)
            ->setOrder('event_end_date', 'DESC');

        $page = 1;
        $pageSize = 15;
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

}
