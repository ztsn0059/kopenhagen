<?php
  namespace Zehntech\ProductApiXml\Controller\Import;
  
  use Zehntech\ProductApiXml\Helper\Parser;
  
  class Upload extends \Magento\Framework\App\Action\Action
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
        \Zehntech\Remmer\Model\RemmerFileFactory $modleFactory,
        \Zehntech\ProductApiXml\Logger\Logger $logger,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ves\Brand\Model\Brand $brand,
        \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollection,
        \Magento\Framework\App\ResourceConnection $resource,
        \Zehntech\ProductApiXml\Helper\ImportParser $importParser,
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
         $this->modleFactory = $modleFactory;
         $this->_logger = $logger;
         $this->_eavSetupFactory = $eavSetupFactory;
         $this->_storeManager = $storeManager;
         $this->_attributeFactory = $attributeFactory;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->brand = $brand;
         $this->brandCollection = $brandCollection;
         $this->_resource = $resource;
         $this->importParser = $importParser;
    }

  public function execute()
    {
      
      $resultJson = $this->resultJsonFactory->create();
      $fileData = $this->importParser->getValue();
      if($fileData){
          $count = 0;
          $arr_product = [];
          foreach ($fileData as $value => $row) 
          {
            if($value>0){
            $newProduct = false;  
            $sku = $row[0];
            $name = utf8_encode($row[1]);
            $quantity = $row[2];
            $price = $row[3];
            $oem = $row[4];
            $brand = utf8_encode($row[5]);
            $description = utf8_encode($row[6]);
            $shortDescription = utf8_encode($row[7]);
            $barcode = $row[8];
            $imageUrl = $row[9];
            $minQty = $row[10];
            $popular = $row[11] ? $row[11]:0;
            $new = $row[12] ? $row[12]:0;
            $offer = $row[13] ? $row[13]:0;
            $onWeb = true;
            $categoryArray = array(array('parent' => utf8_encode($row[14]),'child' => utf8_encode($row[15])),array('parent' => utf8_encode($row[16]),'child' => utf8_encode($row[17])),array('parent' => utf8_encode($row[18]),'child' => utf8_encode($row[19])));
              
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
                $product->setPrice($price);
                $product->setCost($price);
                $product->setWebsiteIds(array(1));
                $product->setStoreId(0); 
                $product->setShowProduct(4);
        
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

                  if($oem){
                    $product->setOem($oem);
                  }
                  $product->setNew($new);
                  $product->setPopular($popular);

                  if($minQty){
                    $product->setMinsales($minQty);
                  }
                  
                  if($imageUrl){
                    $product->setImageurl($imageUrl);
                  }
                  $brandId = 0;
                  if($brand)
                  {
                    $brandId = $this->getBrand($brand,$product);
                  }
                  
                  foreach ($categoryArray as $key => $value) {
                    try{
                      if(!strcmp(trim(strtolower($catArray[$key-1]['child'])),trim(strtolower($value['parent']))))   //execute for all index
                      {
                           $collection = $this->getCategory($value['child'],$parentId);
                           $catId = $collection->getId();
                           if($catId)
                             $assignCat[] = $parentId = $catId;
                           else
                           {
                            $parentId = $this->createCategory($value['child'],$parentId);
                            $assignCat[] = $parentId;
                           }
                      }
                      else
                      {
                           $collection = $this->getCategory($value['parent']);
                           $catId = $collection->getId();
                           if($catId)
                              $assignCat[] = $parentId = $catId;
                           else
                           {
                            $parentId = $this->createCategory($value['parent']);
                            $assignCat[] = $parentId;
                           }
                           $collection = $this->getCategory($value['child'],$parentId);
                           $catId = $collection->getId();
                           if($catId)
                              $assignCat[] = $parentId = $catId;
                           else
                           {
                              $parentId = $this->createCategory($value['child'],$parentId);
                              $assignCat[] = $parentId;
                           }
                        }
                      }
                      catch(\Exception $e)    //for first index
                      {
                       $collection = $this->getCategory($value['parent']);
                       $parentId = $collection->getId();
                       if($parentId)
                          $assignCat[] = $parentId;
                       else
                       {
                          $parentId = $this->createCategory($value['parent']);
                          $assignCat[] = $parentId;
                       }
                       $collection = $this->getCategory($value['child'],$parentId);

                       $catId = $collection->getId();
                       if($catId)
                          $assignCat[] = $parentId = $catId;
                       else
                       {
                          $parentId = $this->createCategory($value['child'],$parentId);
                          $assignCat[] = $parentId;
                       }
                      }
                    }

                    try{
                      $assignCat = array_filter($assignCat);
                      $product->setCategoryIds($assignCat);
                    }catch(\Exception $e)
                    {
                      $this->_logger->info("product of sku - " . $product->getSku() . " categories are not assign");
                    }

                  try{
                      $this->saveStockData($sku,$quantity,$onWeb,$price,'sup-2');
                  }catch(\Exception $e){
                      $arr_product[] = $product->getSku();
                      $this->_logger->info("product of sku - " . $product->getSku() . " stock is not save");
                  }

                  try{
                    $product->save();
                  }catch(Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e){
                    $url = preg_replace('#[^0-9a-z]+#i', '-', $name) . "-". $product->getId();
                    $url = strtolower($url);
                    $product->setUrlKey($url);
                    $product->save();
                  }catch (\Magento\Framework\Exception\CouldNotSaveException $e){
                      $arr_product[] = $product->getSku();
                      $this->_logger->info("product of sku - " . $product->getSku() . " is not save");
                  }catch(\Exception $e){
                    $arr_product[] = $product->getSku();
                    $this->_logger->info("Product - " . $product->getSku() . " is not save");
                  }

                  if($newProduct && $brandId)
                  {
                    $this->saveProductBrand($brandId,$product);
                  }
                }
        }  
        $message = ['status'=>'successfully import']; 
      }
      else
      {
        $message = ['status'=>'nothing to import'];
      }

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info($message);
      return $resultJson->setData($message);

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

    public function getCategoryCollection()    //category collection
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');        
        return $collection;
    }

    public function createCategory($catName,$parentId=false)     //create category
    {
      if($catName){
        if(!$parentId)
          $parentId = $this->getCategory("Test category1")->getId();
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
        }catch (Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e){
          $this->_logger->info("Category - " . $catName . " is not create because url already exist");
        }
        $newCat = $this->getCategoryCollection()
       ->addAttributeToFilter('parent_id',$parentId) 
       ->addAttributeToFilter('name', $catName);
          if ($newCat->getSize())
          {
            return $newCat->getFirstItem()->getId();
          }
      }
      else
        return 0;
    }

    public function getCategory($name,$parentId=false)
    {
      $collection = $this->getCategoryCollection();
      if($parentId)
        $collection->addAttributeToFilter('parent_id',array("eq"=>$parentId));
      $collection->addAttributeToFilter('name',array("eq"=>$name));
      return $collection->getFirstItem();
    }

 
    public function getBrand($brand,$product)
    {
      $name = trim($brand);
      $brandId = 0;
      $brandCollection = $this->brand->getCollection();
      foreach($brandCollection as $brand){
        if(strtolower($brand->getName())==strtolower($name)){
          $brandId = $brand->getId();
        }
      }
      if($brandId){
        $data = $this->brand->load($brandId);
      }
      else{
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
        try{
          $this->brand->setData($brandData);
          $this->brand->save();
         }catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
           $this->_logger->info("brand - " . $brand . " is not create");
         } 
       }
      $brandCollection = $this->brand->getCollection();
      foreach($brandCollection as $brand){
        if(strtolower($brand->getName())==strtolower($name)){
          $brandId = $brand->getId();
          break;
        }
      }
      if($brandId){
        $brand_model = $this->brand->load($brandId);
        $brand_model->saveProduct($product->getId());
      }
      return $brandId;
    }

    public function saveProductBrand($brandId,$product)
    {
      $productId = $product->getId();
      $connection = $this->_resource->getConnection();
      $table_name = $this->_resource->getTableName('ves_brand_product');
      if($brandId && $productId){
         $connection->query('INSERT INTO ' . $table_name . ' VALUES ( ' . $brandId . ', ' . (int)$productId . ',0)');
      }
    }

}
?>
