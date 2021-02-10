<?php

namespace Zehntech\Test\Controller\Check;

class Check extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\File\Csv $csv,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->moduleDirReader = $moduleDirReader;
        $this->directory_list = $directory_list;
        $this->csv = $csv;
        $this->stockRegistry = $stockRegistry;

    }
    public function execute()
    {
        // $file = $this->directory_list->getPath('media')."/zip_codes/zip_code.csv";
        // $params = $this->getRequest()->getParams();
        // $countryId = $params['countrId'];
        // $zipCode =  $params['zip_code'];
        // if($countryId=='DK'){
        //     if (($handle = fopen($file, 'r')) === false) {
        //         die('Error opening file');
        //     }
        //     $headers = fgetcsv($handle, 1024, ',');
        //     $complete = array();

        //     while ($row = fgetcsv($handle, 1024, ',')) {
        //         $complete[] = array_combine($headers, $row);
        //     }
        //     fclose($handle);
        //     $key = array_search($zipCode,array_column($complete, 'zip_code'));
        //     if(is_numeric($key)){
        //         $resultJson = $this->resultJsonFactory->create();
        //         return $resultJson->setData(['city_name' => $complete[$key]['city_name']]);
        //     }
        // }

        
        
    }
}