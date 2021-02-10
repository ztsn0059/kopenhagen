<?php
namespace Zehntech\ProductApiXml\Controller\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends \Magento\Framework\App\Action\Action
{


    protected $_categoryCollectionFactory;
    protected $category;
    protected $helper;
    protected $proCollection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Zehntech\ProductApiXml\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Zehntech\ProductApiXml\Helper\ImportParser $importParser,
        \MGS\Brand\Model\Brand $mgsBrand,
        DirectoryList $directory_list,
        \Magento\Catalog\Model\ProductFactory $_productFact,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Catalog\Api\Data\ProductLinkInterface $linkInterface,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->category = $category;
        $this->_logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->importParser = $importParser;
        $this->mgsBrand = $mgsBrand;
        $this->directory_list = $directory_list;
        $this->_productFact = $_productFact;
        $this->productCollection = $productCollectionFactory;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->_sourceItemFactory = $sourceItemFactory;
        $this->file = $file;
        $this->_linkInterface = $linkInterface;
    }

    public function execute()
    {

        $supplierName = $this->getRequest()->getParam('supplier');

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("start time - " . date('m/d/Y h:i:s a', time()));
        $resultJson = $this->resultJsonFactory->create();
        $fileData = $this->importParser->getValue($supplierName);
        $tmpDir = $this->directory_list->getPath(DirectoryList::MEDIA).'/custom_image/';
        $this->file->checkAndCreateFolder($tmpDir);
        if ($fileData) {

            $head = $fileData[0];
            unset($fileData[0]);
            $head = array_flip($head);
            $count = 0;
            $arr_product = [];

            $supplierId = $this->getOptionIdByLabel('supplier', $supplierName);
            $this->proCollection = $this->productCollection->create();
            $this->proCollection->addAttributeToSelect('*')->addAttributeToFilter('supplier', array('neq' => $supplierId));

            foreach ($fileData as $value => $row) {

                if ($value > 0) {

                    $newProduct = false;
                    $sku = $row[$head['SKU']];
                    $name = utf8_encode($row[$head['Name']]);

                    if (!$name)
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
                    $popular = $row[$head['Popular']] ? ($row[$head['Popular']] == "Yes" ? 1 : 0) : 0;
                    $supplier = $row[$head['SupplierName']];
                    $packing = $row[$head['Packing']];
                    $outerPack = $row[$head['OuterPack']];
                    $katalogPage = $row[$head['KatalogPage']];
                    $stockUnit = $row[$head['StockUnit']];
                    $relatedProducts = $row[$head['Accessories']];

                    $onWeb = true;
                    $categoryArray = array(array('parent' => utf8_encode($row[$head['HeadCategory1']]), 'child' => utf8_encode($row[$head['Category1']])), array('parent' => utf8_encode($row[$head['HeadCategory2']]), 'child' => utf8_encode($row[$head['Category2']])), array('parent' => utf8_encode($row[$head['HeadCategory3']]), 'child' => utf8_encode($row[$head['Category3']])));


                    try {

                        $product = $this->productRepository->get($sku);
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {

                        $product = $this->productFactory->create();
                        $product->setSku($sku);
                        $newProduct = true;
                    }

                    /*related products*/
                    $reletedProductSkus = [];
                    if ($relatedProducts) {

                        $relatedProducts = explode(',', $relatedProducts);

                        foreach ($relatedProducts as $counterKey => $related) {

                            $reletedProductSkus[$sku][] = $related;

                        }
                    }

                    /*related products*/

                    $product->setName($name);
                    $product->setPrice($price);
                    $product->setCost($price);

                    if ($newProduct) {

                        $product->setAttributeSetId(4);
                        $product->setTypeId('simple');
                        $product->setWebsiteIds(array(1));
                        $product->setStoreId(0);
                        $product->setTaxClassId(2);
                        $product->setVisibility(4);
                        $product->setStatus(1);
                        $product->setShowProduct(4);

                        $product->setPopular($popular);
                        if ($oem) {
                            $product->setOem($oem);
                        }

                        if ($supplier) {
                            $supplierId = $this->getOptionIdByLabel('supplier', $supplier);
                            $product->setSupplier($supplierId);
                        }

                        try {
                            if ($brand) {
                                $brandId = $this->getBrand($brand);
                                if (!$brandId) {
                                    $brandId = $this->createBrand($brand);
                                }
                                $product->setMgsBrand($brandId);
                            }
                        } catch (\Exception $e) {
                            $this->_logger->info("Product - " . $product->getSku() . " brand not create");
                        }

                        if ($supplier == "Remmer") {
                            foreach ($categoryArray as $key => $value) {
                                try {
                                    if (!strcmp(trim(strtolower($catArray[$key - 1]['child'])), trim(strtolower($value['parent']))))   //execute for all index
                                    {
                                        $collection = $this->getCategory($value['child'], $parentId);
                                        $catId = $collection->getId();
                                        if ($catId)
                                            $assignCat[] = $parentId = $catId;
                                        else {
                                            $parentId = $this->createCategory($value['child'], $parentId);
                                            $assignCat[] = $parentId;
                                        }
                                    } else {
                                        $collection = $this->getCategory($value['parent']);
                                        $catId = $collection->getId();
                                        if ($catId)
                                            $assignCat[] = $parentId = $catId;
                                        else {
                                            $parentId = $this->createCategory($value['parent']);
                                            $assignCat[] = $parentId;
                                        }
                                        $collection = $this->getCategory($value['child'], $parentId);
                                        $catId = $collection->getId();
                                        if ($catId)
                                            $assignCat[] = $parentId = $catId;
                                        else {
                                            $parentId = $this->createCategory($value['child'], $parentId);
                                            $assignCat[] = $parentId;
                                        }
                                    }
                                } catch (\Exception $e)    //for first index
                                {
                                    $collection = $this->getCategory($value['parent']);
                                    $parentId = $collection->getId();
                                    if ($parentId)
                                        $assignCat[] = $parentId;
                                    else {
                                        $parentId = $this->createCategory($value['parent']);
                                        $assignCat[] = $parentId;
                                    }
                                    $collection = $this->getCategory($value['child'], $parentId);

                                    $catId = $collection->getId();
                                    if ($catId)
                                        $assignCat[] = $parentId = $catId;
                                    else {
                                        $parentId = $this->createCategory($value['child'], $parentId);
                                        $assignCat[] = $parentId;
                                    }
                                }
                            }

                            try {
                                $assignCat = array_filter($assignCat);
                                $product->setCategoryIds($assignCat);
                                $assignCat = [];
                            } catch (\Exception $e) {
                                $this->_logger->info("product of sku - " . $product->getSku() . " categories are not assign");
                            }
                        }
                    }


                    if ($description) {
                        $product->setDescription($description);
                    }
                    if ($shortDescription) {
                        $product->setShortDescription($shortDescription);
                    }
                    /* custom attributes */
                    if ($barcode) {
                        $product->setBarcode($barcode);
                    }
                    if ($katalogPage) {
                        $product->setKatalogpage($katalogPage);
                    }
                    if ($stockUnit) {
                        $product->setStockunit($stockUnit);
                    }
                    if ($outerPack) {
                        $product->setOuterpack($outerPack);
                    }
                    if ($packing) {
                        $product->setPacking($packing);
                    }
                    if ($minQty) {
                        $stockData = [
                            'use_config_manage_stock' => 0, 
                                'manage_stock' => 1,
                                'is_in_stock' => $quantity ? 1 : 0, 
                                'qty' => $quantity 
                            ];
                        if ($minQty > 1){
                            $stockData['qty_increments'] = $minQty;
                            $stockData['min_sale_qty'] = $minQty;
                            $stockData['enable_qty_increments'] = 1;
                        }
                        $product->setStockData($stockData);
                    }

                    // image code
                    if($imageUrl && ($newProduct || $imageUrl!=$product->getImageurl())){
                     try{
                        $product->setImageurl($imageUrl);
                        $imageUrl = is_numeric(strpos($imageUrl,'http')) ? $imageUrl : 'https://'.$imageUrl;
                        $newFileName = $tmpDir . baseName($imageUrl);
                        $result = $this->file->read($imageUrl, $newFileName);
                        
                        if ($result) {
                           $product->addImageToMediaGallery($newFileName, array('image', 'small_image', 'thumbnail'), false, false);
                        }
                      }catch(\Exception $e){
                          $this->_logger->info("product of sku - " . $product->getSku() . " image is not save");
                      }
                    }
                    // image code


                   try {
                        $product->save();
                    } catch (\Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
                        $url = preg_replace('#[^0-9a-z]+#i', '-', $name) . "-" . $sku;
                        $url = strtolower($url);
                        $product->setUrlKey($url);
                        $product->save();
                    } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
                        $arr_product[] = $product->getSku();
                        $this->_logger->info("product of sku - " . $product->getSku() . " is not save first " . $e->getMessage());
                    } catch (\Exception $e) {
                        $arr_product[] = $product->getSku();
                        $this->_logger->info("Product - " . $product->getSku() . " is not save second " . $e->getMessage());
                    }

                }
            }

            /*releted products*/
            if(count($reletedProductSkus)){
                foreach ($reletedProductSkus as $mainProductSku => $relatedSkus) {

                    $mainProductList[] = $mainProductSku;
                    $relatedCatch = 'No Error';

                    $product = $this->productRepository->get($mainProductSku);

                    foreach ($relatedSkus as $relatedSku) {

                        try {

                            $linkedProduct = $this->productRepository->get($relatedSku);

                            if ($linkedProduct) {

                                $linkData = $this->_linkInterface
                                    ->setSku($mainProductSku)
                                    ->setLinkedProductSku($relatedSku)
                                    ->setLinkType("related");

                                $linkDataAll[] = clone $linkData;
                            }

                        } catch (\Exception $e) {

                            $relatedCatch = $e->getMessage();

                        }


                    }
                    if ($linkData) {

                        $product->setProductLinks($linkDataAll);
                    }
                    $product->save();
                    $linkDataAll = [];

                }
            }else{
                $relatedCatch = "No related products";
                $mainProductList = "No products";
            }

            /*releted products*/

            $message = ['status' => 'successfully import', 'supplier' => $supplierName, 'relatedProducts' => $relatedCatch, 'mainProductsList' => $mainProductList];

        } else {
            $message = ['status' => 'nothing to import'];
        }
        $logger->info("end time - " . date('m/d/Y h:i:s a', time()));
        $logger->info($message);
        return $resultJson->setData($message);

    }


    public function getCategoryCollection()    //category collection
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        return $collection;
    }

    public function createCategory($catName, $parentId = false)     //create category
    {
        if ($catName) {
            if (!$parentId)
                $parentId = $this->getCategory("Produkter")->getId();
            $parentCategory = $this->category->create()->load($parentId);
            $menu = true;
            if ($parentCategory->getLevel() > 4)
                $menu = false;
            $category = $this->category->create();
            $category->setPath($parentCategory->getPath())
                ->setParentId($parentCategory->getId())
                ->setName($catName)
                ->setIncludeInMenu($menu)
                ->setIsActive(true);
            $category->setCustomAttributes([
                'disabled_children' => 0,
                'custom_apply_to_products' => 0,
                'level_column_count' => 'level_column_count',
                'disabled_children' => 0,
                'category_is_link' => 1
            ]);
            try {
                $category->save();
            } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
                $this->_logger->info("Category - " . $catName . " is not create");
            } catch (Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException $e) {
                $this->_logger->info("Category - " . $catName . " is not create because url already exist");
            }
            $newCat = $this->getCategoryCollection()
                ->addAttributeToFilter('parent_id', $parentId)
                ->addAttributeToFilter('name', $catName);
            if ($newCat->getSize()) {
                return $newCat->getFirstItem()->getId();
            }
        } else
            return 0;
    }

    public function getCategory($name, $parentId = false)
    {
        $collection = $this->getCategoryCollection();
        if ($parentId)
            $collection->addAttributeToFilter('parent_id', array("eq" => $parentId));
        $collection->addAttributeToFilter('name', array("eq" => $name));
        return $collection->getFirstItem();
    }


    public function createBrand($brandName)
    {
        $mgsbrand = $this->mgsBrand;
        $brandData = array(
            'name' => $brandName,
            'url_key' => strtolower(preg_replace('#[^0-9a-z]+#i', '-', $brandName)),
            'status' => 1,
            'is_featured' => 0,
            'sort_order' => 0,
            'stores' => array(0),
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
            if (strtolower($brand->getName()) == strtolower($name)) {
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

    public function getOptionIdByLabel($attributeCode, $optionLabel)
    {
        $_product = $this->_productFact->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }

}

?>
