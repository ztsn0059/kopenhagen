<?php

namespace Zehntech\Test\Controller\Module;

class BrandTest extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Ves\Brand\Model\Brand $brand,
        \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $brandCollection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Category $category,
        \Magento\Catalog\Api\CategoryRepositoryInterface $repository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \MGS\Brand\Model\Brand $mgsBrand,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Zehntech\ProductApiXml\Controller\Import\Upload $upload,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->brand = $brand;
        $this->brandCollection = $brandCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
        $this->_repository  = $repository;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollectionFactory;
        $this->mgsBrand = $mgsBrand;
        $this->productFactory = $productFactory;
        $this->upload = $upload;
    }

      public function execute()
      {
        //set brand code
        // $sku = "10000740";
        // $name = "Star";
        // $product = $this->productRepository->get($sku);
        // $product->setMgsBrand($this->getBrand($name));
        // $product->save();
      //set brand code

        // $brandName = "Toysshopee";
        
        // print_r($this->createBrand($brandName));

      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/entrylog.log');
      $logger = new \Zend\Log\Logger();
      $logger->addWriter($writer);
      $logger->info("start time - ".date('m/d/Y h:i:s a', time()));



      for($i=0;$i<45;$i++)
      {
          print_r($i);
      }
      $logger->info("end time - ".date('m/d/Y h:i:s a', time()));
        die("hello");
      
      // return $resultJson->setData(date('m/d/Y h:i:s a', time()));

        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('mgs_brand',76);
        print_r($collection->getData());
        // foreach ($collection as $key => $value) {
        //   print_r($value->getData());
        // }
        die("hello");



      }


      public function createBrand($brandName)
      {
        if(!$this->getBrand($brandName))
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