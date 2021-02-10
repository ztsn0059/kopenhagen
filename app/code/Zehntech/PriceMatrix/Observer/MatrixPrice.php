<?php

 

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

 

namespace Zehntech\PriceMatrix\Observer;

 

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Zehntech\PriceMatrix\Model\ResourceModel\Grid\CollectionFactory;
use Zehntech\PriceMatrix\Model\ResourceModel\MediumMatrix\CollectionFactory as mediumMatrixCollection;
use Zehntech\PriceMatrix\Model\ResourceModel\LargeMatrix\CollectionFactory as largeMatrixCollection;

 

class MatrixPrice implements ObserverInterface
{

 

    protected $_request;
    protected $_tierPrice;
    protected $_smallMatrix;
    protected $_mediumMatrix;
    protected $_largeMatrix;
    protected $_priceForSmallGroup;
    protected $_priceForMediumGroup;
    protected $_priceForLargeGroup;
    protected $_priceForNotLoggedInGroup;

 

    const SMALL_GROUP_ID = 1;
    const MEDIUM_GROUP_ID = 3;
    const LARGE_GROUP_ID = 2;
    const NOT_LOGGEDIN_GROUP_ID = 0;
    const QTY = 1;

 

    public function __construct(ProductTierPriceInterfaceFactory $tierPrice, CollectionFactory $smallMatrix, mediumMatrixCollection $mediumMatrix, largeMatrixCollection $largeMatrix,\Magento\Framework\App\RequestInterface $request)
    {
        $this->_tierPrice = $tierPrice;
        $this->_smallMatrix = $smallMatrix;
        $this->_mediumMatrix = $mediumMatrix;
        $this->_largeMatrix = $largeMatrix;
        $this->request = $request;
    }

 

    /*
     * setting product price by customer group
     */

 

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $productCost = (float)$product->getCost();
        $productPrice = (float)$product->getPrice();
        $requestObj = $this->request->getParam('product');
        if(array_key_exists('tier_price',$requestObj))
            $tierPrices = $requestObj['tier_price'];
        if ($productCost):

 

            $smallMatrixCollection = $this->_smallMatrix->create();
            $mediumMatrixCollection = $this->_mediumMatrix->create();
            $largeMatrixCollection = $this->_largeMatrix->create();

 

            $smallMatrixFilteredData = $smallMatrixCollection
                ->addFieldToFilter('min_price', ['lt' => $productCost])
                ->addFieldToFilter('max_price', ['gt' => $productCost])
                ->getColumnValues('markup');

 

            $mediumMatrixFilteredData = $mediumMatrixCollection
                ->addFieldToFilter('min_price', ['lt' => $productCost])
                ->addFieldToFilter('max_price', ['gt' => $productCost])
                ->getColumnValues('markup');

 

            $largeMatrixFilteredData = $largeMatrixCollection
                ->addFieldToFilter('min_price', ['lt' => $productCost])
                ->addFieldToFilter('max_price', ['gt' => $productCost])
                ->getColumnValues('markup');

 

               
                if (!empty($smallMatrixFilteredData) && !empty($mediumMatrixFilteredData) && !empty($largeMatrixFilteredData)) {

 

                $this->_priceForNotLoggedInGroup = (float)(($productCost * $smallMatrixFilteredData[0]) / 100) + $productCost;
                $this->_priceForSmallGroup = (float)(($productCost * $smallMatrixFilteredData[0]) / 100) + $productCost;
                $this->_priceForMediumGroup = (float)(($productCost * $mediumMatrixFilteredData[0]) / 100) + $productCost;
                $this->_priceForLargeGroup = (float)(($productCost * $largeMatrixFilteredData[0]) / 100) + $productCost;

 

                $product->setPrice($this->_priceForNotLoggedInGroup);     
                $priceByCustomer = [

 

                    $this->_tierPrice->create()
                        ->setCustomerGroupId(self::SMALL_GROUP_ID)
                        ->setQty(self::QTY)
                        ->setValue($this->_priceForSmallGroup),

 

                    $this->_tierPrice->create()
                        ->setCustomerGroupId(self::MEDIUM_GROUP_ID)
                        ->setQty(self::QTY)
                        ->setValue($this->_priceForMediumGroup),

 

                    $this->_tierPrice->create()
                        ->setCustomerGroupId(self::LARGE_GROUP_ID)
                        ->setQty(self::QTY)
                        ->setValue($this->_priceForLargeGroup),

 

                    $this->_tierPrice->create()
                        ->setCustomerGroupId(self::NOT_LOGGEDIN_GROUP_ID)
                        ->setQty(self::QTY)
                        ->setValue($this->_priceForSmallGroup)
                ];
            if(array_key_exists('tier_price',$requestObj))
            {
                foreach ($tierPrices as $key => $price) {
                if($price['price_qty']>1)
                {
                    $tierPrice = $this->_tierPrice->create();
                    $tierPrice->setCustomerGroupId($price['cust_group'])
                    ->setQty($price['price_qty']);
                    if(array_key_exists('percentage_value', $price))
                    {
                        if(!$tierPrice->getExtensionAttributes())
                        {
                            $tierPrice->setExtensionAttributes();
                        }
                        $tierPrice->getExtensionAttributes()->setPercentageValue($price['percentage_value']);
                    }
                    else
                    {
                        $tierPrice->setValue($price['price']);
                    }
                    $priceByCustomer[] = $tierPrice;
                }
            }
        }
        
        $product->setTierPrices($priceByCustomer);
        }

 

        endif;

 

    }

 

}