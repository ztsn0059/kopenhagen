<?php

namespace Zehntech\MinSales\Observer;


use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
class CartSaveAfterObserver implements ObserverInterface
{
	public function __construct(RequestInterface $request)
	{
		$this->_request = $request;
	}

    public function execute(EventObserver $observer)
    {
    	$item = $observer->getEvent()->getData('quote_item');
	    $product = $item->getProduct();
	    $qty = $product->getMinsales()>0 ? $product->getMinsales()*$item->getQty() : $item->getQty();
	    $item->setQty($qty);
	    die("hello");
    }
}