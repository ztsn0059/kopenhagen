<?php
  namespace Zehntech\Reorder\Block;
  
  use Zehntech\Reorder\Model\Layer\Resolver;

  class OrderDetails extends \Magento\Framework\View\Element\Template
  {
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context);
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

    public function getLastOrderDate($sku) {
        $orders = $this->getOrderCollection();
        foreach ($orders as $key => $order) {
            foreach ($order->getAllItems() as $keyNum => $item) {
                if($item->getSku()==$sku)
                    return $order->getCreatedAt();
            }
        }
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