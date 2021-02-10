<?php

namespace Zehntech\SalesOrderQtyAfter\Plugin;

class PaymentQtyDeduct
{
	public function __construct(
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Sales\Api\Data\OrderInterface $order,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
	){
		$this->request = $request;
		$this->order = $order;
		$this->_stockRegistry = $stockRegistry;
	} 

	public function afterExecute(\PensoPay\Payment\Controller\Payment\Callback $callback)
	{
		$body = $this->request->getContent();

            $response = Json::decode($body);
            if ($response->accepted === true) {
            	$order = $this->order->loadByIncrementId($response->order_id);
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

        return $result;
	}
}