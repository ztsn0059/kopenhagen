<?php
  namespace Zehntech\ProductApiXml\Controller\Despec;
  // For Despec
  use Zehntech\ProductApiXml\Helper\DespecParser;
  class Test extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        array $data = []
    ) {
         parent::__construct($context);
         $this->parser = $parser;
         $this->resultJsonFactory = $resultJsonFactory;
         $this->csvProcessor = $csvProcessor;
         $this->fileDriver = $fileDriver;
    }

    public function execute()
    { 
      $data = $this->parser->getValue();
      $count = 0;
      foreach ($data as $row) {
        $row = utf8_encode($row);

        $row = preg_split("/\t/", $row);
        if($count==0)
          {
            $this->skuIn = array_search('"ItemId"',$row);
            $this->qtyIn = array_search('"StockActual"',$row);
            $this->oemIn = array_search('"ItemOEM"',$row);
            $this->nameIn = array_search('"Item"',$row);
            $this->short_descIn = array_search('"ItemTxt"',$row);
            $this->descIn = array_search('"ItemWebDescription"',$row);
            $this->stkUnitIn = array_search('"Unit"',$row);
            $this->expectedDeliveryIn = array_search('"ExpectedDate"',$row);
            $this->priceIn = array_search('"Price"',$row);
            $this->brandIn = array_search('"Brand"',$row);
            $this->weightIn = array_search('"Weight"',$row);
            $this->heightIn = array_search('"Height"',$row);
            $this->minQtyIn = array_search('"MinQ"',$row);
            $this->categoryIn = array_search('"ItemTypeTxt"',$row);
          }
          else
          {
              $sku =  trim($row[$this->skuIn],'"');
              $name = trim($row[$this->nameIn],'"');
              $oem = trim($row[$this->oemIn],'"');
              $qty = $row[$this->qtyIn];
              $short_desc = trim($row[$this->short_descIn],'"');
              $desc = trim($row[$this->descIn],'"');
              $price = $row[$this->priceIn];
              $brand = trim($row[$this->brandIn],'"');
              $stockUnit = trim($row[$this->stkUnitIn],'"');
              $expectedDate = trim($row[$this->expectedDeliveryIn],'"');
              $weight = $row[$this->weightIn];
              $height = $row[$this->heightIn];
              $minQty = $row[$this->heightIn];
              $category = trim($row[$this->categoryIn],'"');
              // print_r($category);
          }
        $count++;
      }
      // print_r($count-1);

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

      $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
      $categories = $categoryCollection->create();
      $categories->addAttributeToSelect('*');

      foreach ($categories as $cat) {
        print_r($cat->getUrlKey());
        echo "<br>";
      }
      die("hello");


      
      $resultJson = $this->resultJsonFactory->create();
      $message = array('data' => "succes message");
      return $resultJson->setData($message);
    }
  }