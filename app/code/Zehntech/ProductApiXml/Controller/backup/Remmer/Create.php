<?php
  namespace Zehntech\ProductApiXml\Controller\Remmer;
  
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
            $collection = $this->createCat("default");
            foreach ($collection as $category) {
                print_r($category);
            }

            $resultJson = $this->resultJsonFactory->create();
            $message = array('status'=>'successfully done');
            return $resultJson->setData($message);
    }

    public function createCat($catName)
    {

        $collection = $this->_categoryCollectionFactory
                ->create()
                ->addAttributeToFilter('name',$catName)
                ->getFirstItem();
                print_r($collection);
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


}

