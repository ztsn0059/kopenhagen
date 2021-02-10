<?php

namespace Zehntech\Test\Controller\Module;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
class Create extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Api\CategoryRepositoryInterface $repository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $proFactory,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemExtensionFactory $SourceItemExtensionFactory,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
        $this->_repository  = $repository;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->productFactory = $productFactory;
        $this->_sourceItemFactory = $sourceItemFactory;
        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->SourceItemExtensionFactory = $SourceItemExtensionFactory;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->file = $file;
        $this->directory_list = $directory_list;
        $this->_stockRegistry = $stockRegistry;
    }

    public function execute()
    {


        echo "<pre>";
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('add_to_nav',1);
        print_r($collection->getFirstItem()->getData());
        

        echo "</pre>";
        die("hello");
        // $sku = '4000030';
        // $product = $this->productRepository->get($sku);
        // // $stockItem=$this->_stockRegistry->getStockItem($product->getId());
        // // $product = $this->productFactory->create();
        // // $product->setSku($sku);
        // // $product->setName("test stock product");
        // $product->setStockData(['qty_increments' => 20, 'min_sale_qty' => 20,'enable_qty_increments'=>1]);
        // $product->save();
        // // $stockItem=$this->_stockRegistry->getStockItemBySku($sku);
        // // $stockItem->setData('qty_increments',9);
        // // $stockItem->setData('min_sale_qty',9);
        // // $stockItem->save();
        // // $product->save();
        // // print_r($stockItem->getData());
        // // print_r($product->getStockData());
        // die("hello");
        // $source = $this->getSourceStock($product);
        // print_r($source);
        // die("hello");

        // $cat = $this->category->load(241);
        // // print_r($cat->getUrl());
        // $collection = $this->_categoryCollectionFactory->create();
        // $collection->addAttributeToSelect('*');
        // foreach ($collection as $key => $value) {
        //     print_r($value->getUrl());
        //     echo "<br>";
        // }
        // die("hello");




        // $sku = "test4";
        // $name = "test product for image".$sku;
        // $imageUrl = "https://www.remmer.dk/images/imagehandler.ashx?path=/product-images/1103314.jpg";
        // $price = 20;


        //     try {
        //       $product = $this->productRepository->get($sku);
        //       $isNew = false;
        //     } 
        //     catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //       $product = $this->productFactory->create();
        //       $product->setSku($sku);
        //       $newProduct = true;
        //     }

        //     $product->setName($name); 
        //     $product->setPrice($price);
        //     $product->setCost($price);

        //     $product->setAttributeSetId(4);
        //     $product->setTypeId('simple');
        //     $product->setWebsiteIds(array(1));
        //     $product->setStoreId(0); 
        //     $product->setTaxClassId(2);
        //     $product->setVisibility(4);
        //     $product->setStatus(1);
        //     $product->setShowProduct(4);


        // $sourceItems = [];
        // $sourceItem = $this->_sourceItemFactory->create();
        // $sourceItem->setSourceCode('default');
        // $sourceItem->setSku($sku);
        // $sourceItem->setQuantity(0);
        // $sourceItem->setStatus(false);
        // $sourceItem->setCost(0);
        // $sourceItems[] = $sourceItem;
        // $this->_sourceItemsSaveInterface->execute($sourceItems);


        // $tmpDir = $this->directory_list->getPath(DirectoryList::MEDIA).'/custom_image';
        // $this->file->checkAndCreateFolder($tmpDir);
        // $newFileName = $tmpDir . baseName($imageUrl);
        // $result = $this->file->read($imageUrl, $newFileName);
        
        // if ($result) {
        //    $product->addImageToMediaGallery($newFileName, array('image', 'small_image', 'thumbnail'), false, false);
        // }
        // $product->save();

}

    public function getSourceStock($product)
    {
        $supplierId = $product->getSupplier();
        $attr = $product->getResource()->getAttribute('supplier');
         if ($attr->usesSource()) {
               $option = $attr->getSource()->getOptionText($supplier);
         }
        $supplier = strtolower($option);
        $data = $this->sourceDataBySku->execute($product->getSku());

        foreach ($data as $key => $source) {
            if($source['source_code']==$supplier)
                return $source;
        }
    }
  

}