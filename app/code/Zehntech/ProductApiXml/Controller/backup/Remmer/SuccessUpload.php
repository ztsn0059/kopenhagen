<?php
  namespace Zehntech\ProductApiXml\Controller\Remmer;
  
  use Zehntech\ProductApiXml\Helper\Parser;
  
  class Check extends \Magento\Framework\App\Action\Action
  {

    protected $parser;
    protected $getSourceData;
    protected $_categoryCollectionFactory;
    protected $category;
    protected $_eavConfig;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $category,
        Parser $parser,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Zehntech\Remmer\Model\RemmerFileFactory $modleFactory,
        \Zehntech\ProductApiXml\Logger\Logger $logger,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ves\Brand\Model\Brand $brand,
        \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollection,
        array $data = []
    ) {
         parent::__construct($context);
         $this->productRepository = $productRepository;
         $this->productFactory = $productFactory;
         $this->parser = $parser;
         $this->_categoryCollectionFactory = $categoryCollectionFactory;
         $this->category = $category;
         $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
         $this->_sourceItemFactory = $sourceItemFactory;
         $this->messageManager = $messageManager;
         $this->modleFactory = $modleFactory;
         $this->_logger = $logger;
         $this->_eavSetupFactory = $eavSetupFactory;
         $this->_storeManager = $storeManager;
         $this->_attributeFactory = $attributeFactory;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->brand = $brand;
         $this->brandCollection = $brandCollection;
    }

  public function execute()
    {
      $resultJson = $this->resultJsonFactory->create();
      $fileData = $this->parser->getValue();
      if($fileData){
          $clientId = $fileData['ClientID'];
          $itemData = $fileData['Item'];
          $count = 0;
          $arr_product = [];
          foreach($itemData as $singleData)
          {
            $sku = $singleData['DistPartNumber'];
            $name = $singleData['ProductName'];
            $cost = $singleData['Price'];
            $popular = ($singleData['Popular']=='Yes') ? 1:0;
            $new = ($singleData['New']=='Yes') ? 1:0;
            $barcode = $singleData['barcode'];
            $enviroCode = array_key_exists('EnviroCode', $singleData) ? str_replace("&quot;","'",$singleData['EnviroCode']) : 0;
            $expectedDelivery = array_key_exists('ExpectedDelivery', $singleData) ? $singleData['ExpectedDelivery'] : 0;
            $oem = array_key_exists('OEM', $singleData) ? $singleData['OEM'] : 0;
            $katalogPage = array_key_exists('KatalogPage', $singleData) ? $singleData['KatalogPage'] : 0;
            $recPrice1 = array_key_exists('RecPrice1', $singleData) ? (float)$singleData['RecPrice1'] : 0;
            $recPrice2 = array_key_exists('RecPrice2', $singleData) ? (float)$singleData['RecPrice2'] : 0;
            $itemName3 = array_key_exists('ItemName3', $singleData) ? $singleData['ItemName3'] : 0;
            $manufactPartNumber = array_key_exists('ManufactPartNumber', $singleData) ? $singleData['ManufactPartNumber'] : 0;
            $minSales = array_key_exists('MinSales', $singleData) ? $singleData['MinSales'] : 0;
            $packing = array_key_exists('Packing', $singleData) ? $singleData['Packing'] : 0;
            $outerPack = array_key_exists('OuterPack', $singleData) ? $singleData['OuterPack'] : 0;
            $headCategoryText =  array_key_exists('HeadCategoryText', $singleData) ? $singleData['HeadCategoryText'] : 0;
            $headCategoryText2 =  array_key_exists('HeadCategoryText2', $singleData) ? $singleData['HeadCategoryText2'] : 0;
            $headCategoryText3 =  array_key_exists('HeadCategoryText3', $singleData) ? $singleData['HeadCategoryText3'] : 0;
            $categoryText = array_key_exists('CategoryText', $singleData) ? $singleData['CategoryText'] : 0;
            $categoryText2 = array_key_exists('CategoryText2', $singleData) ? $singleData['CategoryText2'] : 0;
            $categoryText3 = array_key_exists('CategoryText3', $singleData) ? $singleData['CategoryText3'] : 0;
            $imgUrl = array_key_exists('ImageURL', $singleData) ? $singleData['ImageURL'] : 0;
            $brand = array_key_exists('Brand', $singleData) ? $singleData['Brand'] : 0;
            $quantity = array_key_exists('Stock', $singleData) ? $singleData['Stock'] : 0;
            $onWeb = ($singleData['OnWeb']=='Yes') ? 1:0;
            $stockUnit = array_key_exists('StockUnit', $singleData) ? $singleData['StockUnit'] : "";
            $description = array_key_exists('ExtText', $singleData) ? $singleData['ExtText'] : "";
            $shortDescription = array_key_exists('Synonym', $singleData) ? $singleData['Synonym'] : "";
            
            
            try {
              $product = $this->productRepository->get($sku);
              $isNew = false;
            } 
            catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
              $product = $this->productFactory->create();
              $product->setSku($sku);
            }


          
            
          $product->setName($name); 

          $product->setAttributeSetId(4);
          $product->setStatus($onWeb);
          $product->setVisibility(4);
          $product->setTaxClassId(2);
          $product->setTypeId('simple');
          $product->setPrice($cost);
          $product->setCost($cost);
          $product->setWebsiteIds(array(1));
          $product->setStoreId(0); 
          
          if($description)
          {
            $product->setDescription($description);
          }
          if($shortDescription)
          {
            $product->setShortDescription($shortDescription);
          }
          /* custom attributes */
          if($barcode){
            $product->setBarcode($barcode);
          }
          if($clientId){
          $product->setClientid($clientId);
          }
          if($oem){
          $product->setOem($oem);
          }
          if($enviroCode){
          $product->setEnvirocode($enviroCode);
          }
          $product->setNew($new);
          $product->setPopular($popular);

          if($itemName3){
          $product->setItemname3($itemName3);
          }
          if($manufactPartNumber){
          $product->setManufactpartnumber($manufactPartNumber);
          }
          if($minSales){
          $product->setMinsales($minSales);
          }
          if($packing){
          $product->setPacking($packing);
          }
          if($outerPack){
          $product->setOuterpack($outerPack);
          }
          if($katalogPage){
          $product->setKatalogpage($katalogPage);
          }
          if($imgUrl){
          $product->setImageurl($imgUrl);
          }
          if($stockUnit){
          $product->setStockunit($stockUnit);
          }
          $product->setShowProduct(4);
          
        if($recPrice1){
          $product->setData('recprice1', $recPrice1);
        }

        if($recPrice2){
            $product->setData('recprice2', $recPrice2);
        }

        if($expectedDelivery)
        {
            $expectedDelivery = date_create($expectedDelivery);
            $product->setExpecteddelivery($expectedDelivery);
        }

          /*  category work start */
          $categoryIds = [];
          try{
            if($headCategoryText)
            {
          $getCatCollection1 = $this->getCategoryCollection()
                    ->addAttributeToFilter('name', $headCategoryText);
                    if ($getCatCollection1->getSize())
                    {
                      $parent_id1 = $getCatCollection1->getFirstItem()->getId();
                      $categoryIds[] = $getCatCollection1->getFirstItem()->getId();

                      $getChildCatCollection1 = $this->getCategoryCollection()
                      ->addAttributeToFilter('parent_id', $parent_id1)
                      ->addAttributeToFilter('name', $categoryText);

                        if ($getChildCatCollection1->getSize())
                        {
                          $categoryIds[] = $getChildCatCollection1->getFirstItem()->getId(); 
                        }
                        else
                        {    

                          $parentId = $parent_id1;
                          $categoryIds[] = $this->createCategory($parentId,$categoryText); //child
                        }
                    }
                    else
                    {
                      $parentId = '2';
                      $newParentId = $this->createCategory($parentId,$headCategoryText);
                      $categoryIds[] = $newParentId;
                      $categoryIds[] = $this->createCategory($newParentId,$categoryText);
                    }
                  }
          if($headCategoryText2)
          {
          $getCatCollection2 = $this->getCategoryCollection()
                    ->addAttributeToFilter('name', $headCategoryText2);
                  
                      if ($getCatCollection2->getSize())
                      {
                        $parent_id2 = $getCatCollection2->getFirstItem()->getId();
                        $categoryIds[] = $getCatCollection2->getFirstItem()->getId();
                        $getChildCatCollection2 = $this->getCategoryCollection()
                        ->addAttributeToFilter('parent_id', $parent_id2)
                        ->addAttributeToFilter('name', $categoryText2);

                        if ($getChildCatCollection2->getSize())
                        {
                          $categoryIds[] = $getChildCatCollection2->getFirstItem()->getId();
                        }
                        else
                        {
                          $parentId = $parent_id2;
                          $categoryIds[] = $this->createCategory($parentId,$categoryText2);  //child
                        }
                      }
                      else
                      {
                        $parentId = '2';
                        $newParentId = $this->createCategory($parentId,$headCategoryText2);
                        $categoryIds[] = $newParentId;
                        $categoryIds[] = $this->createCategory($newParentId,$categoryText2);
                      }
            }
          if($headCategoryText3)
          {           
          $getCatCollection3 = $this->getCategoryCollection()
                    ->addAttributeToFilter('name', $headCategoryText3);

                      if ($getCatCollection3->getSize())
                      {
                        $parent_id3 = $getCatCollection3->getFirstItem()->getId();
                        $categoryIds[] = $getCatCollection3->getFirstItem()->getId();
                        $getChildCatCollection3 = $this->getCategoryCollection()
                        ->addAttributeToFilter('name', $categoryText3);
                        if ($getChildCatCollection3->getSize())
                        {
                          $categoryIds[] = $getChildCatCollection3->getFirstItem()->getId();
                        }
                        else
                        {
                          $parentId = $parent_id3;
                          $categoryIds[] = $this->createCategory($parentId,$categoryText3);   //child
                        }
                      }
                      else
                      {
                        $parentId = '2';
                        $newParentId = $this->createCategory($parentId,$headCategoryText3);
                        $categoryIds[] = $newParentId;
                        $categoryIds[] = $this->createCategory($newParentId,$categoryText3);
                      }
                }
                      $categoryIds = array_unique($categoryIds); 

                      $product->setCategoryIds($categoryIds);
                  }catch(\Magento\Framework\Exception\LocalizedException $e)
                  {
                    $this->_logger->info("category - ". $brand ." is not set");
                  }
                      /* category work end */
                    try{
                        $this->saveStockData($sku,$quantity,$onWeb,$cost,'sup-2');     //stock data
                        $this->saveStockData($sku,0,0,0,'default');     //stock data
                    }catch(\Magento\Framework\Exception $e)
                    {
                      $this->_logger->info("stock of product of sku - " . $sku . "is not set");
                    }

                      /*  brand create if not exist in database */
                    if($brand)
                    {
                    $label = $brand;
                    $attributeCode = 'brand';
                    $attributeInfo1=$this->_attributeFactory->getCollection()
                             ->addFieldToFilter('attribute_code',['eq'=> $attributeCode])
                             ->getFirstItem();
                      $allOptions = $attributeInfo1->getSource()->getAllOptions();
                  
                      $count = 0;
                      foreach($allOptions as $option)
                      {
                        if($option['label']==$label)
                        {
                          $brandId = $option['value'];
                          $count++;
                          break;
                        }
                      }
                      
                      
                      if($count==0)
                      {
                        try
                        {
                      $attribute_arr = [$label];
                      $attributeInfo=$this->_attributeFactory->getCollection()
                             ->addFieldToFilter('attribute_code',['eq'=> $attributeCode])
                             ->getFirstItem();
                             $attribute_id = $attributeInfo->getAttributeId();
                      $option=array();
                      $option['attribute_id'] = $attributeInfo->getAttributeId();
                      foreach($attribute_arr as $key=>$value){
                        $option['value'][$value][0]=$value;
                      }
                      $eavSetup = $this->_eavSetupFactory->create();
                      $eavSetup->addAttributeOption($option);
                      
                      $allOptions = $attributeInfo->getSource()->getAllOptions();
                
                      foreach($allOptions as $option)
                      {
                        if($option['label']==$label)
                        {
                          $brandId = $option['value'];
                          break;
                       }
                    
              
                      }
                     
                    }catch(\Magento\Framework\Exception\StateException $e)
                       {
                         $this->_logger->info("brand " . $brand . " is not create");
                       }
                    } 
                      $product->setBrand($brandId);
                      $count=0;
                      


                  }
                  if($brand)
                  {
                    $this->getBrand($brand,$product);

                  }
                      try{
                        $product->save();    //save product
                    
                      }catch(\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e)
                      {
                        $url = preg_replace('#[^0-9a-z]+#i', '-', $name) . "-". $product->getId();
                        $url = strtolower($url);
                        $product->setUrlKey($url);
                        $product->save(); 
                      }              
                    catch (\Magento\Framework\Exception\CouldNotSaveException $e)
                    {
                      $arr_product[] = $product->getSku();
                      $this->_logger->info("product of sku - " . $product->getSku() . " is not save");
                    }
                    catch (\Magento\Framework\Exception $e)
                    {
                      $arr_product[] = $product->getSku();
                      $this->_logger->info("product of sku - " . $product->getSku() . " is not save");
                    }
                    
                      $count++;
                    $categoryIds = [];

        }
            if(!sizeOf($arr_product))
              $message = ['status'=>'Successfully import'];
            else
              $message = ['status' => 'Successfully import', 'error'=>$count . " products not import",'list'=>$arr_product];
            
            $modelDataObj = $this->modleFactory->create();
            $id = $modelDataObj->getCollection()->addFieldToFilter('status',1)->getFirstItem()->getId();
            $modelDataObj->load($id);
            $modelDataObj->setData('status',0)->save();

          
      }
      else
      {
        $message = ['status'=>'nothing to import'];
      }

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info($message);
      // return $this;
      return $resultJson->setData($message);
}
  



    public function getCategoryCollection()    //category collection
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');        
        
        return $collection;
    }




    public function createCategory($parentId,$catName)     //create category
    {
      // $parentId = '2';
      $parentCategory = $this->category->create()->load($parentId);
      $category = $this->category->create();
      $category->setPath($parentCategory->getPath())
      ->setParentId($parentCategory->getId())
      ->setName($catName)
      ->setIsActive(true);
      try{
        $category->save();
      }catch (\Magento\Framework\Exception\CouldNotSaveException $e){
        $this->_logger->info("Category - " . $catName . " is not create"); 
      }
      $newCat = $this->getCategoryCollection()
    ->addAttributeToFilter('name', $catName);
        if ($newCat->getSize())
        {
          return $newCat->getFirstItem()->getId();
        }
    }



    public function saveStockData($sku,$quantity,$onWeb,$cost,$sourceCode)  //stock source function
    {
      $sourceItem = $this->_sourceItemFactory->create();
      $sourceItem->setSourceCode($sourceCode);
      $sourceItem->setSku($sku);
      $sourceItem->setQuantity($quantity);
      $sourceItem->setStatus($onWeb);
      $sourceItem->setCost($cost);
      $this->_sourceItemsSaveInterface->execute([$sourceItem]);
    }

 
 
public function getBrand($brand,$product)
    {
      
                    $name = trim($brand);
                      $brandId = 0;
                      $brandCollection = $this->brand->getCollection();
                      foreach($brandCollection as $brand)
                      {
                      if(strtolower($brand->getName())==strtolower($name))
                      {
                          $brandId = $brand->getId();
                      }
                      }
                      if($brandId)
                      {
                          $data = $this->brand->load($brandId);
                      }
                      else
                      {
                           $brandData = array('name' => $name,
                          'url_key' => $name,
                          'group_id'  => 1,
                          'image' => 'ves/brand/unset.png',
                          'thumbnail' => 'ves/brand/unset.png',
                          'page_layout' => '2columns-left',
                          'status' => 1,
                          '_first_store_id' => 1,
                          'store_code' => 'default',
                          'store_id'=> 0 
                          );
                          $this->brand->setData($brandData);
                          $this->brand->save();
                       }
                  
                      $brandCollection = $this->brand->getCollection();
                      foreach($brandCollection as $brand)
                      {
                      if(strtolower($brand->getName())==strtolower($name))
                      {
                          $brandId = $brand->getId();
                          break;
                      }
                      }
                      if($brandId)
                      {
                          $brand_model = $this->brand->load($brandId);
                          $brand_model->saveProduct($product->getId());
                      }

                  
    }


}
?>
