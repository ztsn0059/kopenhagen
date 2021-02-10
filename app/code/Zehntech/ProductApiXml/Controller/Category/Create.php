<?php
  namespace Zehntech\ProductApiXml\Controller\Category;
  
  use Zehntech\ProductApiXml\Helper\Parser;
  
  class Create extends \Magento\Framework\App\Action\Action
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

  
      $catArray = array(array('parent' => "demo_8",'child' => ""),array('parent' => "category2171",'child' => ""),array('parent' => "category112171 ",'child' => ""));
      $assignCat = [];


      foreach ($catArray as $key => $value) {
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
    print_r(array_filter($assignCat));
      $message = ["status" => "success"];
      return $resultJson->setData($message);
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


}
?>
