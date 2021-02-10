<?php
  namespace Zehntech\Reorder\Helper;
  
  use Zehntech\Reorder\Model\Layer\Resolver;

  class OrderDetails 
  {
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function getTotalOrderedQty($sku) {
        $orders = $this->getOrderCollection();
        $qty = 0;
        foreach ($orders as $key => $order) {
            foreach ($order->getAllItems() as $keyNum => $item) {
                if($item->getSku()==$sku)
                    $qty += $item->getQtyOrdered();
            }
        }
        return $qty;
    }

    public function getOrderCollection() {
        $customerId = $this->customerSession->getCustomer()->getId();
        $orderList = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at',
            'desc'
        );
        return $orderList;
    }
}