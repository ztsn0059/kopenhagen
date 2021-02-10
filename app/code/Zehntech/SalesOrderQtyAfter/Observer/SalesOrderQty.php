<?php

namespace Zehntech\SalesOrderQtyAfter\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderQty implements ObserverInterface
{

    protected $_stockRegistry;

    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    )
    {
        $this->_stockRegistry = $stockRegistry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $order = $observer->getEvent()->getOrder();
        $method = $order->getPayment()->getMethod();
        if($method == 'pensopay'){
            $order->setStatus('pending_payment');
        }
        if($method != 'pensopay'){
            foreach ($order->getAllItems() as $item) {

                $sku = $item->getProduct()->getSku();
                $stockItem = $this->_stockRegistry->getStockItemBySku($sku);

                if ($stockItem->getQty() >= $item->getQtyOrdered()) {

                    $qtyToUpdate = $stockItem->getQty() - $item->getQtyOrdered();
                    $stockItem->setQty($qtyToUpdate);
                    $this->_stockRegistry->updateStockItemBySku($sku, $stockItem);
                }

            }
        }
        $order->save();

    }

}
