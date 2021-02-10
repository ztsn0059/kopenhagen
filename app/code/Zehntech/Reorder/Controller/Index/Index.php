<?php
  namespace Zehntech\Reorder\Controller\Index;
  
  use Zehntech\Reorder\Model\Layer\Resolver;

  class Index extends \Magento\Framework\App\Action\Action
  {
    protected $_coreRegistry = null;
    private $layerResolver;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->layerResolver = $layerResolver;
        $this->_coreRegistry = $coreRegistry;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

  public function execute()
    {
        if(!$this->customerSession->isLoggedIn()){
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }
        $skuArray = $this->getAllOrderedItemsSku();
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('sku',array('in'=>$skuArray));
        $collectionLayer = $this->_coreRegistry->register('reorder_collection', $collection); 
        if($collectionLayer){
            if($this->layerResolver->get('ordered')===null)
            $this->layerResolver->create('ordered');
        }
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }


    public function getAllOrderedItemsSku()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $orderList = $this->_orderCollectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $customerId
        )->setOrder(
            'created_at',
            'desc'
        );
        $skuArray = [];
        foreach ($orderList as $key => $order) {
            foreach ($order->getAllItems() as $keyNum => $item) {
                $skuArray[] = $item->getSku();
            }
        }
        $skuArray = array_values(array_unique($skuArray));
        return $skuArray;
    }
}