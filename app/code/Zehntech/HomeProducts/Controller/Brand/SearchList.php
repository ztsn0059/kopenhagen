<?php

namespace Zehntech\HomeProducts\Controller\Brand;

class SearchList extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->productCollection = $productCollectionFactory;
    }
    public function execute()
        {
        $brandId = $this->getRequest()->getParam('brandId');
        $keySearch = $this->getRequest()->getParam('keySearch'); 

        $productList = [];
        $resultJson = $this->resultJsonFactory->create();
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*');
        if($brandId)
            $collection->addAttributeToFilter('mgs_brand',$brandId);
        if(strlen($keySearch))
              $collection->addAttributeToFilter('name',array('like' => '%'.$keySearch.'%'));  
        foreach ($collection as $product) {
            $productList[] = array(
                'name'=>$product->getName(),
                'url'=>$product->getProductUrl()
            );
        }
        if($brandId==0&&strlen($keySearch)==0)
            $productList = []; 
        $list = ["productList"  => $productList];
        return $resultJson->setData($list);
    }

}   