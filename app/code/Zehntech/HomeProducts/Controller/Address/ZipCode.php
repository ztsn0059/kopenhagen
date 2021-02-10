<?php

namespace Zehntech\HomeProducts\Controller\Address;

class ZipCode extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\File\Csv $csv,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->directory_list = $directory_list;
        $this->csv = $csv;
    }
    public function execute()
    {
        $file = $this->directory_list->getPath('media')."/zip_codes/zip_code.csv";
        $params = $this->getRequest()->getParams();
        $countryId = $params['countrId'];
        $zipCode =  $params['zip_code'];
        $status = false;
        if($countryId=='DK'){
            $data = $this->csv->getData($file);
            foreach ($data as $key => $row) {
                if($row[1]==$zipCode){
                    $status = true;
                    break;
                }
            }
             $resultJson = $this->resultJsonFactory->create();
            if(is_numeric($key) && $status){
                $res = ['city_name' => $data[$key][2]];
                return $resultJson->setData($res);
            }
            return $resultJson->setData(['city_name' => '']);

        }
    }
}