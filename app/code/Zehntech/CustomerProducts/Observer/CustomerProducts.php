<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\CustomerProducts\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
class CustomerProducts implements ObserverInterface
{
    private $_productCollection;
    
    private $newcollection  = [];
  
    public function __construct(CollectionFactory $productCollection)
    {
        $this->_productCollection = $productCollection;
    }
    

    public function execute(\Magento\Framework\Event\Observer $observer) {

      $collection = $observer->getEvent()->getCollection();
      foreach($collection as $product)
        {
            print_r($product->getId());
            echo "&nbsp;&nbsp;";
            print_r($product->getSku());
            echo "&nbsp;&nbsp;";
            print_r($product->getShowProduct());
            echo "<br>";
            // echo "<br>";
            // die("hello123");
        }
      die("jukebox");
     $collection->addAttributeToSelect('*')->addAttributeToFilter('show_product',["eq"=>8]);
      print_r(sizeOf($collection));
      die("hello1234");
      //$collection->addAttributeToFilter('price',["gt"=>7]);
      /* print_r(sizeOf($collection));
      die("h78965hello"); */
      /* foreach($collection as $product)
        {
            if(isset($product['access_usser']))
            {  
                $pr_user = $product->getAccessUsser();
            
                if($pr_user[0]==8)
                {
                    array_push($this->newcollection, $product);
                }        
            }
        } */
        //die("hello1278");

      /*   print_r(sizeOf($this->newcollection));
        die("hello"); */

        return $collection;

    }

}