<?php
namespace Zehntech\ProductApiXml\Helper;  
      class DespecParser 
         {
                /**
                 * @var \Magento\Framework\Module\Dir\Reader
                 */
                protected $moduleDirReader;

                /**
                 * @var \Magento\Framework\Xml\Parser
                 */
                private $parser;

                public function __construct(
                    \Magento\Framework\Module\Dir\Reader $moduleDirReader,
                    \Magento\Framework\Xml\Parser $parser,
                    \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                    \Zehntech\Despec\Model\DespecFileFactory $modleFactory,
                    array $data = []
                )
                {
                    $this->moduleDirReader = $moduleDirReader;
                    $this->parser = $parser;
                    $this->directory_list = $directory_list;
                    $this->modleFactory = $modleFactory;
                }

                public function getValue() 
                {
                    $modelDataObj = $this->modleFactory->create();
                    $firstData = $modelDataObj->getCollection()->addFieldToFilter('status','1')->getLastItem();
                    
                    if($firstData->getData()){
                        $file = $firstData->getFile();
                        $filePath = $this->directory_list->getPath('pub') . '/media/' . $file;
                        // print_r($filePath);
                        // die("hello");
                        // $data = readfile($filePath);
                        // $data = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        // print_r($filePath);
                        $my_file = fopen($filePath, "rw");
                        $arr = [];
                        while (! feof ($my_file)) 
                      { 
                      $arr[] = fgets($my_file);
                      // echo "<br><br>"; 
                      } 
                      return $arr;
                      print_r($arr);
                        die("hello");
                        return $data;
                        // $data = array_map('str_getcsv', file($filePath));
                        // $lines = array();
                        // $fopen = fopen($filePath, 'r');
                        // while (!feof($fopen)) {
                        //     $line=fgets($fopen);
                        //     $line=trim($line);
                        //     $lines[]=$line;

                        // }
                        // fclose($fopen);
                        // $finalOutput = array();
                        // foreach ($lines as $string)
                        // {
                        //     $string = preg_replace('!\s+!', '  ', $string);
                        //     $row = explode('"  "', $string);
                        //     array_push($finalOutput,$row);
                        // }
                        // return $finalOutput;
                    }
                    return 0;
                }
         }