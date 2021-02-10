<?php

namespace Zehntech\Test\Controller\Module;

class Test extends \Magento\Framework\App\Action\Action
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
        $resultJson = $this->resultJsonFactory->create(); 
        // $this->remmer();
        // $this->despec();
        // $this->also();
        // $this->mmd();
        $this->csvRead();
        // $this->readDir();
// $this->getConvert('5426"');
        // $pathFile = $this->directory_list->getPath('media').'/zehntech/images/';
        // print_r($pathFile);

        // $url = "https://www.remmer.dk/images/imagehandler.ashx?path=/product-images/4002450.jpg";
        // $img = $this->directory_list->getPath('media').'/zehntech/images/1st.jpg';


        // file_put_contents($img,file_get_contents($url));
    die("hello");




        return $resultJson->setData("success");
    }

    public function remmer()
    {
        $path = $this->directory_list->getPath('media').'/zehntech/import/remmer.xml';
        $parsedArray = $this->parser->load($path)->xmlToArray();
        foreach ($parsedArray['StockTable']['Item'] as $key => $value) {
          print_r($value);
          echo "<br><br>";
        }
    }
    public function despec()
    {
      $path = $this->directory_list->getPath('media').'/zehntech/import/despec.txt';
      $data = file($path);
      foreach ($data as $key => $value) {
        $value = utf8_encode($value);
        $value = preg_split('/\t/',$value);
        print_r($value);
        echo "<br><br>";
      }
    }
    public function also()
    {
      $path = $this->directory_list->getPath('media').'/zehntech/import/also.txt';
      $data = file($path);
      // print_r($data);
      foreach ($data as $key => $value) {
       $value = utf8_encode($value);
        $value = preg_split('/\t/',$value);
        print_r($value);
        echo "<br><br>";
      }
    }
  
    public function mmd()
    {
       $url = "https://www.mmd.dk/webservices/mmd.asmx/ProduktListe";
       $param = [
        'Konto' => '70260760',
        'UserId' => 'XWebservice367',
        'Password' => 'fTTe37lT'
       ];
       $apiCaller = $this->_httpClient->create();
       $apiCaller->setUri($url);
       $apiCaller->setMethod(\Zend_Http_Client::POST);
       $apiCaller->setHeaders([
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: length',
        ]);
       $apiCaller->setParameterPost($param);
       $data = $apiCaller->request();
       print_r($data);
    }

    public function getConvert($str)
    {
      $str1 = $str;
      // $str = preg_replace('/^[0-9]*+"/','' , $str);
      if(preg_match('/^[0-9]*+"/', $str)){
        $str = preg_grep('/^[0-9]*+"/',$str);
      print_r($str);
      }
      echo "<br>";
      // print_r($str1);
    }

    public function csvRead()
    {
      $filePath = '/zehntech/import/stest/techdata-data (2).csv';
      $path = $this->directory_list->getPath('media').$filePath;
      $data = $this->csv->getData($path);
      print_r($data);
     
    }

    public function readDir()
    {
      $path = $this->directory_list->getPath('media').'/zehntech/import/';

      $currentDateTime = time();
      $date2 = date('Y-m-d',$currentDateTime);
      $date2 = date_create($date2);
      $fileArray = scandir($path);
      foreach ($fileArray as $key => $value) {
        if(strpos($value,'.txt'))
        {
          $date = filemtime($path.$value);
          $date1 = date('Y-m-d',$date);
          $date1 = date_create($date1);
          $diff = date_diff($date1,$date2);
          $diff->format("%R%a days");
          // print_r($diff);
          echo "<br>";
        }
      }
      echo "<br>";
      

      die("hello");
    }

}