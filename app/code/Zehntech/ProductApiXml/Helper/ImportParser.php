<?php
namespace Zehntech\ProductApiXml\Helper;  

    class ImportParser 
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
            \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
            \Magento\Framework\File\Csv $csv,
            array $data = []
        )
        {
            $this->moduleDirReader = $moduleDirReader;
            $this->directory_list = $directory_list;
            $this->csv = $csv;
        }

        public function getValue($supplier) 
        {
            $path = '';
            if($supplier=="remmer")
                $path = $this->directory_list->getPath('media').'/zehntech/import/remmer-data.csv';
                // $path = $this->directory_list->getPath('media').'/zehntech/import/samplesheet-remmer.csv';
            if($supplier=="mmd")
                $path = $this->directory_list->getPath('media').'/zehntech/import/samplesheet-mmd.csv';
            if($supplier=="also")
                $path = $this->directory_list->getPath('media').'/zehntech/import/samplesheet-supplier.csv';
            if($supplier=="despec")
                $path = $this->directory_list->getPath('media').'/zehntech/import/split-descpec-partial.csv';
            if($supplier=="techdata")
                $path = $this->directory_list->getPath('media').'/zehntech/import/samplesheet-techdata-1.csv';
            if($path){
                $data = $this->csv->getData($path);
                return $data;
            }
        }
            
    }  
