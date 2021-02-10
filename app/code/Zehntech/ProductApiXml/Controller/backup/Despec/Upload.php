<?php
  namespace Zehntech\ProductApiXml\Controller\Despec;
  // For Despec
  use Zehntech\ProductApiXml\Helper\DespecParser;
  class Upload extends \Magento\Framework\App\Action\Action
  {

    protected $parser;
    protected $getSourceData;
    protected $_categoryCollectionFactory;
    protected $category;
    protected $_eavConfig;
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       DespecParser $parser,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollection,
        \Ves\Brand\Model\Brand $brand,
        \Zehntech\ProductApiXml\Logger\Logger $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
         parent::__construct($context);
         $this->parser = $parser;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->fileDriver = $fileDriver;
         $this->productRepository = $productRepository;
         $this->productFactory = $productFactory;
         $this->parser = $parser;
         $this->_categoryCollectionFactory = $categoryCollectionFactory;
         $this->category = $category;
         $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
         $this->_sourceItemFactory = $sourceItemFactory;
         $this->_logger = $logger;
         $this->_attributeFactory = $attributeFactory;
         $this->brand = $brand;
         $this->brandCollection = $brandCollection;
         $this->_resource = $resource;

    }

    public function execute()
    { 


      $data = $this->parser->getValue();
      $count = 0;
      $temp = 0;
     


       foreach ($data as $row) {
        // $row = utf8_encode($row);
        $row = preg_split("/\t/", $row);
        if(sizeof($row)>1)
        {        
        if($count==0)
          {
            $this->skuIn = array_search('"ItemId"',$row);
            $this->qtyIn = array_search('"StockActual"',$row);
            $this->oemIn = array_search('"ItemOEM"',$row);
            $this->nameIn = array_search('"Item"',$row);
            $this->short_descIn = array_search('"ItemTxt"',$row);
            $this->descIn = array_search('"ItemWebDescription"',$row);
            // $this->stkUnitIn = array_search('"Unit"',$row);
            $this->expectedDeliveryIn = array_search('"ExpectedDate"',$row);
            $this->priceIn = array_search('"Price"',$row);
            $this->brandIn = array_search('"Brand"',$row);
            $this->weightIn = array_search('"Weight"',$row);
            $this->heightIn = array_search('"Height"',$row);
            $this->minQtyIn = array_search('"MinQ"',$row);
            $this->categoryIn = array_search('"ItemTypeTxt"',$row);
            $this->eanIn = array_search('"ItemEAN"',$row);
          }
          else
          {
              $sku =  trim($row[$this->skuIn],'"');
              $name = trim($row[$this->nameIn],'"');
              $oem = trim($row[$this->oemIn],'"');
              $qty = $row[$this->qtyIn];
              $short_desc = trim($row[$this->short_descIn],'"');
              $desc = trim($row[$this->descIn],'"');
              $price = $row[$this->priceIn];
              $brand = trim($row[$this->brandIn],'"');
              // $stockUnit = trim($row[$this->stkUnitIn],'"');
              $expectedDate = trim($row[$this->expectedDeliveryIn],'"');
              $weight = $row[$this->weightIn];
              $height = $row[$this->heightIn];
              $minQty = $row[$this->minQtyIn];
              $category = trim($row[$this->categoryIn],'"');
              $barCode = trim($row[$this->eanIn],'"');
              $newProduct = false;  
              try {
              $product = $this->productRepository->get($sku);
            }
            catch(\Magento\Framework\Exception\NoSuchEntityException $e){
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
            if($desc){
              $product->setDescription($desc);
            }
             if($short_desc){
              $product->setShortDescription($short_desc);
            }
            if($barCode){
              $product->setBarcode($barCode);
            }
            if($oem){
              $product->setOem($oem);
            }
            if($expectedDate)
            {
              $expectedDelivery = date_create($expectedDate);
              $product->setExpecteddelivery($expectedDelivery);
            }

            try{
                  $this->saveStockData($sku,$qty,1,$price,'sup-2');     //stock data
                  $this->saveStockData($sku,0,0,0,'default');     //stock data
              }catch(\Magento\Framework\Exception $e)
              {
                $this->_logger->info("stock of product of sku - " . $sku . "is not set");
              }
          $brandId = 0;
          if($brand)
          {
            $brandId = $this->getBrand($brand,$product);
          }
          // if($newProduct)
          //   print_r("new product");
          // else
          //   print_r("existing product");
          // echo "<br>";
          // category start

          $categoryIds = [];

          if($category)
            {
                  $getCatCollection = $this->getCategoryCollection()
                    ->addAttributeToFilter('name', $category);
                    if ($getCatCollection->getSize())
                    {
                      $parent_id1 = $getCatCollection->getFirstItem()->getId();
                      $categoryIds[] = $getCatCollection->getFirstItem()->getId();
                    }
                    else
                    {
                      $parentId = '2';
                      $categoryIds[] = $this->createCategory($parentId,$category);

                    }
            }
                  try{
                    $product->setCategoryIds($categoryIds);
                  }catch(\Magento\Framework\Exception\LocalizedException $e)
                  {
                    $this->_logger->info("category of - ". $sku ." is not set");
                  }

          //ctagory end

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
                    ++$temp;
                    $categoryIds = [];

                    //again save the product
                        if($newProduct && $brandId){
                         try{
                            $this->saveProductBrand($brandId,$product);
                          }catch(\Magento\Framework\Exception\Exception $e)
                            {
                              $this->_logger->info("brand is not assigned to the product sku - " . $product->getSku());
                            } 
                          } 
                        
                    //again save the product                 
              }
          }
        $count++;
      }
      $resultJson = $this->resultJsonFactory->create();
      $message = array('status'=>'successfully import','products'=>$temp);

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
          return $brandId;
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