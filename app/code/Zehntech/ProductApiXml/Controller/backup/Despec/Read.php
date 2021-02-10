<?php
  namespace Zehntech\ProductApiXml\Controller\Despec;
  //for ALSO
  use Zehntech\ProductApiXml\Helper\DespecParser;
  
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
		    $count = 0;


            foreach ($data as $record) {

                $row = preg_split('/\t/',$record);
                // print_r($row);
                // die("hello");
                if($count==0)
                {
                    $this->skuIn = array_search('ProductID',$row);
                    $this->qtyIn = array_search('AvailableQuantity',$row);
                    $this->nameIn = array_search('ShortDescription',$row);
                    $this->descIn = array_search('Description',$row);
                    $this->expectedDeliveryIn = array_search('AvailableNextDate',$row);
                    $this->priceIn = array_search('NetPrice',$row);
                    $this->category1In = array_search('CategoryText1',$row);
                    $this->category2In = array_search('CategoryText2',$row);
                    $this->category3In = array_search('CategoryText3',$row);
                    $this->manfactpartnumberIn = array_search('ManufacturerPartNumber',$row);
                    $this->brandIn = array_search('ManufacturerName',$row);
                    print_r($this->category2In);

                }
                else
                {
                  $sku =  $row[$this->skuIn];
                  $name = utf8_decode($row[$this->nameIn]);
                  $oem = $row[$this->manfactpartnumberIn];
                  $qty = $row[$this->qtyIn];
                  $desc = utf8_decode($row[$this->descIn]);
                  $price = $row[$this->priceIn];
                  $expectedDate = $row[$this->expectedDeliveryIn];
                  $category1 = utf8_decode($row[$this->category1In]);
                  $category2 = utf8_decode($row[$this->category2In]);
                  $category3 = utf8_decode($row[$this->category3In]);
                  $brand = utf8_decode($row[$this->brandIn]);
                  print_r($category1);
                  echo "&nbsp;&nbsp;&nbsp;";
                  print_r($category2);
                  echo "&nbsp;&nbsp;&nbsp;";
                  print_r($category3);
                  echo "<br>";
                }
                        
                $count++;
            }
                print_r($count);
            
            die("hello");
        }
    }
