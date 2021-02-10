<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\PriceMatrix\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Zehntech\PriceMatrix\Model\ResourceModel\Grid\CollectionFactory;
use Zehntech\PriceMatrix\Model\ResourceModel\MediumMatrix\CollectionFactory as mediumMatrixCollection;
use Zehntech\PriceMatrix\Model\ResourceModel\LargeMatrix\CollectionFactory as largeMatrixCollection;

class ImportMatrixPrice implements ObserverInterface
{

    protected $_request;
    protected $_tierPrice;
    protected $_smallMatrix;
    protected $_mediumMatrix;
    protected $_largeMatrix;
    protected $_priceForSmallGroup;
    protected $_priceForMediumGroup;
    protected $_priceForLargeGroup;
    protected $_productRepository;

    const SMALL_GROUP_ID = 1;
    const MEDIUM_GROUP_ID = 3;
    const LARGE_GROUP_ID = 2;
    const QTY = 1;

    public function __construct(ProductTierPriceInterfaceFactory $tierPrice, CollectionFactory $smallMatrix, mediumMatrixCollection $mediumMatrix, largeMatrixCollection $largeMatrix, ProductRepository $productRepository)
    {
        $this->_tierPrice = $tierPrice;
        $this->_smallMatrix = $smallMatrix;
        $this->_mediumMatrix = $mediumMatrix;
        $this->_largeMatrix = $largeMatrix;
        $this->_productRepository = $productRepository;
    }

    /*
     * setting product price by customer group
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {

            $bunch = $observer->getBunch();

            foreach ($bunch as $product) {

                $getProduct = $this->_productRepository->get($product['sku'], true);
                $productCost = $product['cost'];

                if ($productCost) {

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

                        $this->_priceForSmallGroup = (float)(($productCost * $smallMatrixFilteredData[0]) / 100) + $productCost;
                        $this->_priceForMediumGroup = (float)(($productCost * $mediumMatrixFilteredData[0]) / 100) + $productCost;
                        $this->_priceForLargeGroup = (float)(($productCost * $largeMatrixFilteredData[0]) / 100) + $productCost;

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
                                ->setValue($this->_priceForLargeGroup)
                        ];

                    }
                }


                $getProduct->load($getProduct->getId())
                    ->setTierPrices($priceByCustomer)
                    ->save();

            }

        } catch (\Execption $e) {

            echo $e->getMessage();

        }

    }

}
