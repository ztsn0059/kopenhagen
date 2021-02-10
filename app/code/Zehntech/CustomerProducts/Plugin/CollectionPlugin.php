<?php
namespace Zehntech\CustomerProducts\Plugin;
 
class CollectionPlugin
{
    /**
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     */
    private $_customerSession;
    public function __construct(\Magento\Customer\Model\Session $customerSession)
    {
        $this->_customerSession = $customerSession;
    }

    public function beforeLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, $printQuery = false, $logQuery = false)
    {
        $customerGroup = $this->_customerSession->getCustomer()->getGroupId();
        if(!$subject->isLoaded())
        {
            // print_r($customerGroup);
            // die("hello7896");
         if($customerGroup==1)
         {
            //  die("heloo123");
             $subject->addAttributeToFilter('show_product',[["eq"=>4],["eq"=>5]]);
          
         }
         else if($customerGroup==3)
         {
             $subject->addAttributeToFilter('show_product',[["eq"=>4],["eq"=>6]]);       
         }
         else if($customerGroup==2)
         {
             $subject->addAttributeToFilter('show_product',[["eq"=>4],["eq"=>7]]);       
         }
         else
         //if($customerGroup==0)
         {
             $subject->addAttributeToFilter('show_product',["eq"=>4]);       
         }
        }
 
        return [$printQuery, $logQuery];
    }
}