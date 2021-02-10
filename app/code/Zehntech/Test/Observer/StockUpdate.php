<?php

namespace Zehntech\Test\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku; 
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;


class StockUpdate implements ObserverInterface
{
	public function __construct(
    GetSourceItemsDataBySku $sourceDataBySku,
    CollectionFactory $productCollectionFactory,
    ProductFactory $_productFact,
    \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
    \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory
  )
	{
		$this->sourceDataBySku = $sourceDataBySku;
        $this->productCollection = $productCollectionFactory;
        $this->_productFact = $_productFact;        
	}

	public function execute(EventObserver $observer)
    {

    	$_product = $observer->getProduct();
    	echo "<pre>";
    	$sku = $_product->getSku();
    	$oem = $_product->getOem();
    	$supplierId = $_product->getSupplier();

      $supplier = $this->getOptionLabelById('supplier',$supplierId);

    	$stockData = $this->sourceDataBySku->execute($sku);
    	// foreach ($stockData as $key => $source) {
    	// 	print_r($source);
    	// }
      print_r($stockData->addAttributeToFilter('source_code','mmd'));
    	// $collection = $this->returnProductCollection($sku,$oem,$supplierId);
    	// foreach ($collection as $key => $value) {
    		  
    	// }
    	echo "</pre>";
    	die("hello");
	}

	public function returnProductCollection($sku,$oem,$supplierId)
    {
      $collection = $this->productCollection->create(); 
      $collection->addAttributeToSelect('*')->addAttributeToFilter('supplier',array('neq'=>$supplierId));
      $collection->addAttributeToFilter('oem',$oem)->addAttributeToFilter('sku',array('neq'=>$sku));
      // $product=[];     
      //   foreach ($collection as $key => $_product) {
      //      $product[] = $_product->getSku();
      //    } 
     return  $collection;       
    }

    public function UpdateSupplierSource($sku,$supplier,$qty,$cost)
    {
        $sourceItems = [];
        $sourceItem = $this->_sourceItemFactory->create();
        $sourceItem->setSourceCode('default')->setSku($sku)->setQuantity(0)->setStatus(false)->setCost(0);
        $sourceItems[] = $sourceItem;

        $sourceItem = $this->_sourceItemFactory->create();
        $sourceItem->setSourceCode($supplier)->setSku($sku)->setQuantity($qty)->setStatus(true)->setCost($cost);
        $sourceItems[] = $sourceItem;
        $this->_sourceItemsSaveInterface->execute($sourceItems);

    }

    public function getOptionLabelById($attributeCode,$optionId)
    {
        $_product = $this->_productFact->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttriusesSourcebuteExist->usesSource()) {
            $optionLabel = $isAttributeExist->getSource()->getOptionLabel($optionId);
        }
        return $optionLabel;
    }

}
