<?php
  namespace Zehntech\ProductApiXml\Controller\Despec;
  
  use Zehntech\ProductApiXml\Helper\DespecParser;
  use Zehntech\ProductApiXml\Helper\SimpleXLSX;
  class Read extends \Magento\Framework\App\Action\Action
  {

    protected $parser;
    protected $getSourceData;
    protected $_categoryCollectionFactory;
    protected $category;
    protected $_eavConfig;
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       DespecParser $parser,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        array $data = []
    ) {
         parent::__construct($context);
         $this->parser = $parser;
         $this->resultJsonFactory = $resultJsonFactory;
    }
        public function execute()
        {
           
            $dataArray = [];
            $data = $this->parser->getValue();

            // $data = explode("\n",$data);
            // print_r($data);
            $count = 0;
            // print_r($data);
            // print_r(sizeof($data));
            foreach ($data as $record) {

                // $record = str_replace('""','"',$record);
                // 
                 
                # $record = preg_split('/"|[\n]/', $record, -1, PREG_SPLIT_NO_EMPTY);
                // preg_split ( '/,|;|\||\s/' , $string )
                // $record = trim($record);

                if($count==1){
                    print_r($record);
                    echo "<br><br>";
                    // $record = preg_split('/\"+/',$record);
                    $record = preg_split('/\"+/', $record); 
                    echo "<br><br>";
                    
                    foreach ($record as $key) {
                        print_r($key);
                        echo "<br>";
                    }

                    // if(preg_match('/(d+(\.\d{1,2}))?$/',$key))
                    // {
                    //     $key = preg_split('/[\s]/', $key);
                    //     print_r($key);
                    // }
                    // print_r($record);
                    echo "<br><br>";
                // $record_12 = preg_split('/\s+/', $record[12]);
                // unset($record_12[0]);
                // $record[12]=$record_12;
 


                // foreach ($record as $rowData) {
                //     $rowData = trim($rowData);
                //     if(preg_match_all('/\d+\.|^\d+\s/',$rowData))
                //     {
                //         $rowData = preg_split('/\s+/', $rowData);
                //     }
                //    print_r($rowData);
                //    echo "<br>";
                // }
 
            }
                ++$count;
            


            }
         



            die("hello");
        }



    }
