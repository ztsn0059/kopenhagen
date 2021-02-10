<?php
  namespace Zehntech\ProductApiXml\Controller\Test;
  
  use Zehntech\ProductApiXml\Helper\Parser;
  
  class Uploadcopy extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\App\ResourceConnection $resource,
        \Zehntech\ProductApiXml\Helper\ImportParser $importParser,
        \MGS\Brand\Model\Brand $mgsBrand,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
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
         $this->_resource = $resource;
         $this->importParser = $importParser;
         $this->mgsBrand = $mgsBrand;
         $this->directory_list = $directory_list;
    }

  public function execute()
    {

      $supplierName = $this->getRequest()->getParam('supplier');
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info("start time - ".date('m/d/Y h:i:s a', time()));
      
      $resultJson = $this->resultJsonFactory->create();
      $fileData = $this->importParser->getValue($supplierName);
      if($fileData){
          $head = $fileData[0];
          unset($fileData[0]);
         $head = array_flip($head);
          $count = 0;
          $arr_product = [];
          foreach ($fileData as $value => $row) 
          {
            if($value>0){
            $newProduct = false;  
            $sku = $row[$head['SKU']];
            $name = utf8_encode($row[$head['Name']]);
            if(!$name)
                $name = $sku;
            $quantity = $row[$head['Stock']];
            $price = $row[$head['Price']];
            $oem = $row[$head['Oem']];
            $brand = utf8_encode($row[$head['Brand']]);
            $description = utf8_encode($row[$head['Description']]);
            $shortDescription = utf8_encode($row[$head['ShortDescription']]);
            $barcode = $row[$head['Barcode']];
            $imageUrl = $row[$head['ImageUrl']];
            $minQty = $row[$head['Minqty']];
            $popular = $row[$head['Popular']] ? ($row[$head['Popular']]=="Yes"?1:0):0;
            $new = $row[$head['New']] ? ($row[$head['New']]=="Yes"?1:0):0;
            $offer = $row[$head['Offer']] ? ($row[$head['Offer']]=="Yes"?1:0):0;
            $supplier = $row[$head['SupplierName']];
            $onWeb = true;
            $categoryArray = array(array('parent' => utf8_encode($row[$head['HeadCategory1']]),'child' => utf8_encode($row[$head['Category1']])),array('parent' => utf8_encode($row[$head['HeadCategory2']]),'child' => utf8_encode($row[$head['Category2']])),array('parent' => utf8_encode($row[$head['HeadCategory3']]),'child' => utf8_encode($row[$head['Category3']])));
              
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
        
                if($supplier=="Remmer"){ 
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
                      $assignCat =  [];
                    }catch(\Exception $e)
                    {
                      $this->_logger->info("product of sku - " . $product->getSku() . " categories are not assign");
                    }
                  }

                  try{
                      $this->saveStockData($sku,$quantity,$onWeb,$price,'default');
                      $this->saveStockData($sku,$quantity,$onWeb,$price,$supplierName);
                      // $this->saveStockData($sku,$quantity,$onWeb,$price,'mmd');
                      // $this->saveStockData($sku,$quantity,$onWeb,$price,'remmer');
                      // $this->saveStockData($sku,$quantity,$onWeb,$price,'despec');

                  }catch(\Exception $e){
                      $arr_product[] = $product->getSku();
                      $this->_logger->info("product of sku - " . $product->getSku() . " stock is not save");
                  }


                  try{
                    if($brand)
                    {
                      $brandId = $this->getBrand($brand);
                      if(!$brandId)
                      {
                        $brandId = $this->createBrand($brand);
                      }
                      $product->setMgsBrand($brandId);
                    }
                  }catch(\Exception $e)
                  {
                    $this->_logger->info("Product - " . $product->getSku() . " brand not create");
                  }

                  // try{
                  //   $url = 'https://'.$imageUrl;
                  //   $pathFile = $this->directory_list->getPath('media').'/zehntech/images/'.$sku.'.jpg';

                  //    file_put_contents($pathFile,file_get_contents($url));
                  // }catch(\Exception $e){
                  //     $this->_logger->info("product of sku - " . $product->getSku() . "image is not save");
                  // }


                  try{
                    $product->save();
                  }catch(\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e){
                    $url = preg_replace('#[^0-9a-z]+#i', '-', $name) . "-". $sku;
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
             
                   
                }
        }  
        $message = ['status'=>'successfully import']; 
      }
      else
      {
        $message = ['status'=>'nothing to import'];
      }

      $logger->info("end time - ".date('m/d/Y h:i:s a', time()));
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
      // $sourceItem->getExtensionAttributes()->setCost($cost);
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
          $parentId = $this->getCategory("Produkter")->getId();
        $parentCategory = $this->category->create()->load($parentId);
        $menu = true;
        if($parentCategory->getLevel()>4)
            $menu = false;
        $category = $this->category->create();
        $category->setPath($parentCategory->getPath())
        ->setParentId($parentCategory->getId())
        ->setName($catName)
        ->setIncludeInMenu($menu)
        ->setIsActive(true);
        $category->setCustomAttributes([
          'disabled_children'=> 0,
          'custom_apply_to_products' => 0,
          'level_column_count'=> 'level_column_count',
          'disabled_children'=>0,
          'category_is_link'=>1
        ]);
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

 
  public function createBrand($brandName)
      {
            $mgsbrand = $this->mgsBrand;
             $brandData =  array(
               'name'=>$brandName,
               'url_key'=> strtolower(preg_replace('#[^0-9a-z]+#i', '-', $brandName)),
               'status'=>1,
               'is_featured'=>0,
               'sort_order'=>0,
               'stores'=>array(0),
             );
             $mgsbrand->setData($brandData);
             $mgsbrand->save();
             $brand = $this->_objectManager->create('MGS\Brand\Model\Brand')->load($mgsbrand->getId());
             $optionId = $this->saveOption('mgs_brand', $brand->getName(), (int)$brand->getOptionId());
             $brand->setOptionId($optionId);
             $brand->save();
             return $brand->getOptionId();
      }


      public function getBrand($name)  //return Brand 
      {
        $collection = $this->mgsBrand->getCollection();
        foreach ($collection as $key => $brand) {
          if(strtolower($brand->getName())==strtolower($name))
          {
            return $brand->getOptionId();
          }
        }
      }  

      protected function saveOption($attributeCode, $label, $value)
    {
        $attribute = $this->_objectManager->create('Magento\Catalog\Api\ProductAttributeRepositoryInterface')->get($attributeCode);
        $options = $attribute->getOptions();
        $values = array();
        foreach ($options as $option) {
            if ($option->getValue() != '') {
                $values[] = (int)$option->getValue();
            }
        }
        if (!in_array($value, $values)) {
            return $this->addAttributeOption(
                [
                    'attribute_id' => $attribute->getAttributeId(),
                    'order' => [0],
                    'value' => [
                        [
                            0 => $label,
                        ],
                    ],
                ]
            );
        } else {
            return $this->updateAttributeOption($value, $label);
        }
    }

    protected function addAttributeOption($option)
    {
        $oId = 0;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionTable = $setup->getTable('eav_attribute_option');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int)$optionId;
                if (!$intOptionId) {
                    $data = [
                        'attribute_id' => $option['attribute_id'],
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $setup->getConnection()->insert($optionTable, $data);
                    $intOptionId = $setup->getConnection()->lastInsertId($optionTable);
                    $oId = $intOptionId;
                }
                $condition = ['option_id =?' => $intOptionId];
                $setup->getConnection()->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                    $setup->getConnection()->insert($optionValueTable, $data);
                }
            }
        }
        return $oId;
    }

    protected function updateAttributeOption($optionId, $value)
    {
        $oId = $optionId;
        $setup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
        $optionValueTable = $setup->getTable('eav_attribute_option_value');
        $data = ['value' => $value];
        $setup->getConnection()->update($optionValueTable, $data, ['option_id=?' => $optionId]);
        return $oId;
    }
  


}
?>
