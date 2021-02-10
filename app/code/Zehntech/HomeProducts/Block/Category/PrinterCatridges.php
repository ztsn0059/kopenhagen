<?php
  namespace Zehntech\HomeProducts\Block\Category;
  
  use Magento\Catalog\Helper\Data as taxHelper;
  
  class PrinterCatridges extends \Magento\Framework\View\Element\Template
  {

    protected $_categoryCollectionFactory;
    protected $catId;
    public function __construct(
       \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        taxHelper $taxHelper,
        array $data = []
    ) {
         parent::__construct($context);
         $this->_categoryCollectionFactory = $categoryCollectionFactory;
         $this->_productCollectionFactory = $productCollectionFactory;
         $this->_categoryFactory = $categoryFactory;
         $this->_taxHelper = $taxHelper;
    }
    

        public function getCategories($name)
        {
            $collection = $this->_categoryCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $this->catId = $this->getCtagoeryByName($name);
            $collection->addAttributeToFilter('parent_id',$this->catId);
            return $collection;
        }

        public function getCtagoeryByName($name)
        {
            $collection = $this->_categoryCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $id = $collection->addAttributeToFilter('name',$name)->getFirstItem()->getId();
            return $id;
        }

        public function getAllCategories()
        {
            $collection = $this->_categoryCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            return $collection; 
        }

        public function getProductCollection($id)
        {
            $category = $this->_categoryFactory->create()->load($id);
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoryFilter($category);
            return $collection;
        }

       public function getPriceWithVat($product)
        {
            return $this->_taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
        }
    }