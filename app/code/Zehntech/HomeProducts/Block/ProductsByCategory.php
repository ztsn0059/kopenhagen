<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\HomeProducts\Block;

use \Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Helper\Data as taxHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductsByCategory extends \Magento\Framework\View\Element\Template
{

    protected $_categoryCollection;
    protected $_priceHelper;
    protected $_taxHelper;
    protected $_productCollectionFactory;
    const PARAM_NAME_BASE64_URL = 'r64';
	const PARAM_NAME_URL_ENCODED = 'uenc';

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, CollectionFactory $productCollectionFactory, CategoryFactory $categoryCollection, Data $priceHelper, taxHelper $taxHelper,\Magento\Framework\Data\Form\FormKey $formKey, \Magento\Catalog\Block\Product\ListProduct $listProductBlock,\Magento\Framework\App\Action\Action $action,\MGS\Brand\Model\BrandFactory $brand)
    {
        parent::__construct($context);
        $this->_categoryCollection = $categoryCollection;
        $this->_priceHelper = $priceHelper;
        $this->_taxHelper = $taxHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->formKey = $formKey;
		$this->listProductBlock = $listProductBlock;
		$this->action = $action;
        $this->brand = $brand;
    }

    public function getProductsByCategory($categoryId)
    {
        $category = $this->_categoryCollection->create()->load($categoryId);

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoryFilter($category);
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        $collection->setPageSize(16)->setCurPage(1);

        return $collection;

    }
    public function getPriceWithVat($product)
    {
        return $this->_taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);
    }

    public function getFormattedPrice($price)
    {
        return $this->_priceHelper->currency($price, true, true);
    }
  

    public function getAddToCartPostParams($product)
	{
		return $this->listProductBlock->getAddToCartPostParams($product);
    }
    public function getActionClassObj(){
		return $this->action;
    }
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    public function getCategoryNameById($id)
    {
        return $this->_categoryCollection->create()->load($id)->getName();
    }
    public function getBrand($product)
    {
        $brandCollection = $this->brand->create();
        return $brandCollection->getCollection()->addFieldToFilter('option_id',$product->getMgsBrand())->getFirstItem();
    }

}
