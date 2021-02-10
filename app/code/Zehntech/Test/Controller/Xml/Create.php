<?php   
namespace Zehntech\Test\Controller\Xml;

use Magento\Framework\App\Filesystem\DirectoryList;

class Create extends \Magento\Framework\App\Action\Action
{  
    // const FILE_NAME_ON_FTP = 'test_xml/test.xml';
        public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        DirectoryList $dir
    ) {
         parent::__construct($context);
         $this->sftp = $sftp;
         $this->dir = $dir;
    }

    public function execute()
    {
    //     $domtree = new \DOMDocument('1.0', 'UTF-8');
    //     $orderRequest = $domtree->createElement("xmldata");
    //     $orderRequest = $domtree->appendChild($orderRequest);
     
    //  print_r($this->dir->getPath('media'));
    //  die("hello");
    //     // customer details
    //     $orderHeader = $domtree->createElement("purchaseorder");
    //     $orderHeader = $orderRequest->appendChild($orderHeader);
     
    //     $orderHeader->appendChild($domtree->createElement('CustomerID','1'));
    //     $orderHeader->appendChild($domtree->createElement('CustomerPO','title of song1.mp3'));
    //     $orderHeader->appendChild($domtree->createElement('OrderDateTime','tyujk'));
    //     $orderHeader->appendChild($domtree->createElement('SplitOrder','yes'));
    //     $orderHeader->appendChild($domtree->createElement('ShippingMethod','yes'));
    //     $orderHeader->appendChild($domtree->createElement('RequestedDeliveryDate','yes'));
    //     $orderHeader->appendChild($domtree->createElement('RequestedDeliveryDate','yes'));

    //     $address = ['BillTo'=>1,'ShipTo'=>2];
    //     foreach ($address as $key => $value) {

    //     $billTo = $domtree->createElement($key);
    //     $orderHeader->appendChild($billTo);

    //     $billTo->appendChild($domtree->createElement('Name','yes'));
    //     $billTo->appendChild($domtree->createElement('Company','yes'));
    //     $billTo->appendChild($domtree->createElement('Department','yes'));
    //     $billTo->appendChild($domtree->createElement('Street','yes'));
    //     $billTo->appendChild($domtree->createElement('City','yes'));
    //     $billTo->appendChild($domtree->createElement('StateProvince','yes'));
    //     $billTo->appendChild($domtree->createElement('Zip','yes'));
    //     $billTo->appendChild($domtree->createElement('Country','yes'));
    //     $billTo->appendChild($domtree->createElement('Phone','yes'));
    //     $billTo->appendChild($domtree->createElement('Email','yes'));
    // }
    // // product order details

    //     $orderLine = $domtree->createElement("purchaselines");
    //     $orderRequest->appendChild($orderLine);

    //     $orderLine->appendChild($domtree->createElement('LineNumber','1'));
    //     $orderLine->appendChild($domtree->createElement('VendorSKU','1'));
    //     $orderLine->appendChild($domtree->createElement('WmxSKU','1'));
    //     $orderLine->appendChild($domtree->createElement('Description','1'));
    //     $orderLine->appendChild($domtree->createElement('Qty','1'));
    //     $orderLine->appendChild($domtree->createElement('ExpectedPrice','1'));
    //     $orderLine->appendChild($domtree->createElement('Comment','1'));
    //      /* get the xml printed */
    //     $domtree->preserveWhiteSpace = false;
    //     $domtree->formatOutput = true;
    //     $domtree->save('C:/xampp/htdocs/kopen/pub/media/zehntech/export/m_xml.xml');
    //     // echo $domtree->getData();





        // $data = $domtree->saveXML();
        // try{
        // $this->sftp->open(array('host' => '54.215.246.241','username' => 'ztsl0065','password' => 'FQsGyr0'));
        // $this->sftp->write(self::FILE_NAME_ON_FTP, $data);
        // $this->sftp->close();
        // }catch(\Exception $e){
        //     print_r("can not get connection and upload file");
        // }


        $supplier = "remmer";
        switch ($supplier) {
            case 'techdata': print_r("techdata");
                break;
            case 'also': print_r("also");
                break;
            case 'despec': print_r("despec");
                break;
            case 'mmd': print_r("mmd");
                break;
            case 'remmer': print_r("remmer");
            echo "starting";
                break;
            default: print_r("others");

                break;
        }




    die("hell");


    }
}
