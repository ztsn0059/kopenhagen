<?php

namespace Zehntech\Test\Controller\Module;

class Get extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ProductFactory $_productFact,
        \Magento\InventoryCatalogAdminUi\Observer\SourceItemsProcessor $sourceItemsProcessor,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productCollection = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->_productFact = $_productFact;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->sourceInterface = $sourceInterface;
        $this->_sourceItemFactory = $sourceItemFactory; 
    }

    public function execute()
    {
        $oem = '123-oem1';
        $supplier = "also";
        // $update = true;
        $cost = 12.3;
        $quantity = 87;
        $name =$sku = 'product-also10';
        $skuList = [];
        $productList = $this->getCollection($oem,$sku);
        foreach ($productList as $key => $product) {
              $skuList[] = $product->getSku();  
        }
        if($skuList){
        $sourceData = $this->sourceDataBySku->execute($skuList[0]);
        }
        else
            $sourceData = [];
        try {
          $product = $this->productRepository->get($sku);
          $isNew = false;
        } 
        catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
          $product = $this->productFactory->create();
          $product->setSku($sku);
          $newProduct = true;
        }

        $product->setName($name); 
                $product->setAttributeSetId(4);
                $product->setStatus(1);
                $product->setVisibility(4);
                $product->setTaxClassId(2);
                $product->setTypeId('simple');
                $product->setPrice($cost);
                $product->setCost($cost);
                $product->setWebsiteIds(array(1));
                $product->setStoreId(0); 
                $product->setShowProduct(4);
                $product->setOem($oem);
        $this->crossInventoryToCurrentProduct($sourceData,$sku,$cost,$quantity,$supplier);
        $product->save();

        $data = $this->sourceDataBySku->execute($sku);
        $this->crossInventoryToAllProducts($skuList,$data);

        die("hello data");
    }

    public function getCollection($oem,$sku)
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*');
        return $collection->addAttributeToFilter('oem',$oem)->addAttributeToFilter('sku',array('neq'=>$sku));
        // return $collection->getFirstItem();
    }

    public function crossInventoryToCurrentProduct($data,$sku,$cost,$qty,$supplier)
    {
        if(sizeof($data)==0){
            $data = $this->sourceDataBySku->execute($sku);
             $source = array('source_code'=>'default','quantity'=>0,'status'=>1,'cost'=>0);
            array_push($data,$source);
        }
            $supplier =  strtolower($supplier);
                foreach ($data as $key => $source) {
                if(array_search($supplier,$source,true))
                    {
                        unset($data[$key]);
                        break;
                    }
                }
        
        $source = array('source_code'=>$supplier,'quantity'=>$qty,'status'=>1,'cost'=>$cost);
        array_push($data,$source);
        $data = array_values($data);
        $this->sourceItemsProcessor->process($sku,$data);
    }

    public function crossInventoryToAllProducts(array $skuList,$data)
    {
        foreach ($skuList as $key => $sku) 
        {
            $_product = $this->productRepository->get($sku);
            $this->sourceItemsProcessor->process($sku,$data);
            $_product->save();
        }
    }





    public function stockInventory($sku,$cost,$qty,$supplier)
    {
        $supplier =  strtolower($supplier);
        $data = $this->sourceDataBySku->execute($sku);
        foreach ($data as $key => $source) {
        if(array_search($supplier,$source,true))
            {
                unset($data[$key]);
                break;
            }
        }
        $source = array('source_code'=>$supplier,'quantity'=>$qty,'status'=>1,'cost'=>$cost);
        array_push($data,$source);
        $data = array_values($data);
        $this->sourceItemsProcessor->process($sku,$data);
    }

    public function setStock($sku,$supplier,$qty)
    {
          $sourceItem = $this->_sourceItemFactory->create();
          $sourceItem->setSourceCode($supplier);
          $sourceItem->setSku($sku);
          $sourceItem->setQuantity($qty);
          $sourceItem->setStatus(1);
          $sourceItem->setCost(56);
        $this->sourceInterface->execute([$sourceItem]);
    }

    public function createProduct()
    {
                $name = $sku = "test_product3";
      
        $supplier = "also";
        $qty = 781;
        $cost = 30; 
        $count = 0;
        $price = 45;
       try {
              $product = $this->productRepository->get($sku);
              $isNew = false;
            } 
            catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
              $product = $this->productFactory->create();
              $product->setSku($sku);
              $newProduct = true;
            }

        
                $product->setName($name); 
                $product->setAttributeSetId(4);
                $product->setStatus(1);
                $product->setVisibility(4);
                $product->setTaxClassId(2);
                $product->setTypeId('simple');
                $product->setPrice($cost);
                $product->setCost($cost);
                $product->setWebsiteIds(array(1));
                $product->setStoreId(0); 
                $product->setShowProduct(4);

        // $this->setStock($sku,$supplier,$qty);
                $this->stockInventory($sku,$cost,$qty,$supplier);
        $product->save();
    }


 
}