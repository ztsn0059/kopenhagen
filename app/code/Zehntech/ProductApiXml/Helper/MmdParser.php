<?php
namespace Zehntech\ProductApiXml\Helper;  
      class MmdParser 
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
                    \Zehntech\Mmd\Model\MMdFileFactory $modleFactory,
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
                    $firstData = $modelDataObj->getCollection()->addFieldToFilter('status','1')->getFirstItem();
                    
                    if($firstData->getData()){
                    $file = $firstData->getFile();
                    $filePath = $this->directory_list->getPath('pub') . '/media/' . $file;
               
                    $parsedArray = $this->parser->load($filePath)->xmlToArray();
                    return $parsedArray['ArrayOfProduktInfoClass'];
                    }
                    return 0;
                }
         }