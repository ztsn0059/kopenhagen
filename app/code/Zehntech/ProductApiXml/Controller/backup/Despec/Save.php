<?php
  namespace Zehntech\ProductApiXml\Controller\Despec;
  // For Despec
  use Zehntech\ProductApiXml\Helper\DespecParser;
  class Save extends \Magento\Framework\App\Action\Action
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

        $sku = "test-22";
        $name = "test product22";
        $brand = "test-brand3";
        $price = 11.25;
        $qty = 5010;
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
     
            try{
                $this->saveStockData($sku,$qty,1,$price,'sup-2');     //stock data
                $this->saveStockData($sku,0,0,0,'default');     //stock data
            }catch(\Magento\Framework\Exception $e)
            {
              $this->_logger->info("stock of product of sku - " . $sku . "is not set");
            }
            $product->save();
            if($brand)
            {
                $this->getBrand($brand,$product);
            }
            if($newProduct){
             try{
              $product->save();
            }catch(\Magento\Framework\Exception\AlreadyExistsException $e)
            {
              print_r("expression");
            } 
            } 
            else 
                print_r("old product"); 
         


        print_r("hello");
        die("data");
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



