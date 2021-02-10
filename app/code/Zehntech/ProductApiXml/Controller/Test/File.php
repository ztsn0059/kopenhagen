<?php

namespace Zehntech\ProductApiXml\Controller\Test;

class File extends \Magento\Framework\App\Action\Action
{
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\File\Csv $csv,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\HTTP\ZendClientFactory $httpClient,
        \Zehntech\Remmer\Model\RemmerFileFactory $gridFactory,
        array $data = []
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->moduleDirReader = $moduleDirReader;
        $this->directory_list = $directory_list;
        $this->csv = $csv;
        $this->parser = $parser;
        $this->_httpClient = $httpClient;
        $this->gridFactory = $gridFactory;
    }
    public function execute()
      {
        // $path = "/home/ubuntu/extdata/success/remmer/remmer-data.csv";
        // $data = $this->csv->getData($path);
        // print_r($data);
        //   die("hello");
        $temp = "Yes";
        $count = $temp ? ($temp=="Yes"?1:0):0;
        print_r($count);

    }

}