<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\HomeProducts\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Catalog\Helper\Data as taxHelper;

class PopularProducts extends \Magento\Framework\View\Element\Template
{

    protected $_productCollection;
    protected $_priceHelper;
    protected $_taxHelper;
    const PARAM_NAME_BASE64_URL = 'r64';
	const PARAM_NAME_URL_ENCODED = 'uenc';
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, CollectionFactory $productCollection, Data $priceHelper, taxHelper $taxHelper, \Magento\Framework\Data\Form\FormKey $formKey, \Magento\Catalog\Block\Product\ListProduct $listProductBlock,\Magento\Framework\App\Action\Action $action,\MGS\Brand\Model\BrandFactory $brand)
    {
        parent::__construct($context);
        $this->_productCollection = $productCollection;
        $this->_priceHelper = $priceHelper;
        $this->_taxHelper = $taxHelper;
        $this->formKey = $formKey;
		$this->listProductBlock = $listProductBlock;
		$this->action = $action;
        $this->brand = $brand;
    }

    public function getPopularProducts()
    {
        $collection = $this->_productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('popular', true);

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
    public function getBrand($product)
    {
        $brandCollection = $this->brand->create();
        return $brandCollection->getCollection()->addFieldToFilter('option_id',$product->getMgsBrand())->getFirstItem();
    }

}
