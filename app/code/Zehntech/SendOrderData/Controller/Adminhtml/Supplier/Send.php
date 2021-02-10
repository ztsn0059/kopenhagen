<?php


namespace Zehntech\SendOrderData\Controller\Adminhtml\Supplier;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Send extends \Magento\Backend\App\Action
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,   
        \Magento\Catalog\Model\ProductFactory $_productFactory,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        DirectoryList $dir,
        ResultFactory $resultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->orderRepository = $orderRepository;
        $this->_productFactory = $_productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dir = $dir;
        $this->sftp = $sftp;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager; 
    }


    public function execute()
    {
        $data = $this->getRequest()->getParam('source');
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        
        $suppliers = array_unique(array_column($data, 'name'));
        //filter by source
        foreach ($suppliers as $key => $supplier) {
            if($supplier=='empty')
                continue;
            $supplierId = $this->getOptionIdByLabel($supplier);


            $supplierItem = array_filter($data, function ($var) use ($supplier) {
                return ($var['name'] == $supplier);
            });
            // print_r($supplierItem);
            $collection = $this->getCollection($supplierId)->addAttributeToFilter('oem',array('in'=>array_column($supplierItem, 'oem')));

            $domtree = new \DOMDocument('1.0');
            $orderRequest = $domtree->createElement("xmldata");
            $orderRequest = $domtree->appendChild($orderRequest);

            $orderHeader = $domtree->createElement("purchaseorder");
            $orderHeader = $orderRequest->appendChild($orderHeader);

            $address = $order->getShippingAddress();
            $streetAddress = $address->getStreet();
            $orderHeader->appendChild($domtree->createElement('recid',$order->getCustomerId()));

            $orderHeader->appendChild($domtree->createElement('partnerid',$order->getCustomerId()));
            $orderHeader->appendChild($domtree->createElement('orderref',$order->getId()));

            $orderHeader->appendChild($domtree->createElement('supplierid',''));
            $orderHeader->appendChild($domtree->createElement('name',''));
            $orderHeader->appendChild($domtree->createElement('address',''));
            $orderHeader->appendChild($domtree->createElement('postalcode',''));
            $orderHeader->appendChild($domtree->createElement('city',''));
            $orderHeader->appendChild($domtree->createElement('countryid',''));
            $orderHeader->appendChild($domtree->createElement('phone',''));

            $orderHeader->appendChild($domtree->createElement('deliveryname',$address->getFirstname()));
            $orderHeader->appendChild($domtree->createElement('deliveryaddress',$streetAddress[0]));
            $orderHeader->appendChild($domtree->createElement('deliverypostalcode',$address->getPostcode()));
            $orderHeader->appendChild($domtree->createElement('deliverycity',$address->getCity()));
            $orderHeader->appendChild($domtree->createElement('deliverycountryid',$address->getCountryId()));
            $orderHeader->appendChild($domtree->createElement('yourref',''));
            $orderHeader->appendChild($domtree->createElement('email',$address->getEmail()));
            $orderHeader->appendChild($domtree->createElement('requisitionno',''));
            $orderHeader->appendChild($domtree->createElement('orderdate',$order->getCreatedAt()));

            $orderHeader->appendChild($domtree->createElement('vatno',''));
            $orderHeader->appendChild($domtree->createElement('paymentid',''));
            $orderHeader->appendChild($domtree->createElement('handlingid',''));
            $orderHeader->appendChild($domtree->createElement('shippingid',''));
            $orderHeader->appendChild($domtree->createElement('intrastatcodeid',''));
            $orderHeader->appendChild($domtree->createElement('languageid',''));
            $orderHeader->appendChild($domtree->createElement('currencyid',''));
            $orderHeader->appendChild($domtree->createElement('buyerid',''));
            $orderHeader->appendChild($domtree->createElement('totalweight',''));
            $orderHeader->appendChild($domtree->createElement('totalvolume',''));
            $orderHeader->appendChild($domtree->createElement('totalgoods',''));
            $orderHeader->appendChild($domtree->createElement('totallinediscount',''));
            $orderHeader->appendChild($domtree->createElement('totalgoodsnet',''));
            $orderHeader->appendChild($domtree->createElement('totaldiscount',''));
            $orderHeader->appendChild($domtree->createElement('totalhandling',''));
            $orderHeader->appendChild($domtree->createElement('totalshipping',''));
            $orderHeader->appendChild($domtree->createElement('totalexvat',''));
            $orderHeader->appendChild($domtree->createElement('totalvat',''));
            $orderHeader->appendChild($domtree->createElement('totaltax',''));
            $orderHeader->appendChild($domtree->createElement('totalpayment',''));
            $orderHeader->appendChild($domtree->createElement('totalpurchase',''));
            $orderHeader->appendChild($domtree->createElement('printed',''));
            $orderHeader->appendChild($domtree->createElement('notes',''));
            $orderHeader->appendChild($domtree->createElement('discountpct',''));
            $orderHeader->appendChild($domtree->createElement('countryname',''));
            $orderHeader->appendChild($domtree->createElement('deliverycountryname',''));
            $orderHeader->appendChild($domtree->createElement('paymentname',$order->getPayment()->getMethod()));
            $orderHeader->appendChild($domtree->createElement('shippingname',$order->getShippingMethod()));
            $orderHeader->appendChild($domtree->createElement('handlingname',''));
            $orderHeader->appendChild($domtree->createElement('employeename',''));
            $orderHeader->appendChild($domtree->createElement('orderdate',$order->getCreatedAt()));
            $orderHeader->appendChild($domtree->createElement('shippingdate',''));

            $items = $order->getItems();
            foreach ($items as $keyValue => $item) {
                $_product = $this->getProductByOem($collection,$item->getProduct()->getOem());
                if($_product && $item->getSource()==null){
                   $item->setData('source',$supplier);

                    $orderLine = $domtree->createElement("purchaselines");
                    $orderRequest->appendChild($orderLine);

                    $orderLine->appendChild($domtree->createElement('recid',$keyValue+1));

                    $orderLine->appendChild($domtree->createElement('purchaseid',$order->getId()));

                    $orderLine->appendChild($domtree->createElement('linenumber',''));

                    $orderLine->appendChild($domtree->createElement('productid',$_product->getSku()));

                    $orderLine->appendChild($domtree->createElement('locationid',''));
                    $orderLine->appendChild($domtree->createElement('description',''));
                    $orderLine->appendChild($domtree->createElement('boxes',''));

                    $orderLine->appendChild($domtree->createElement('quantity',$item->getQtyOrdered()));

                    $orderLine->appendChild($domtree->createElement('partreceive',''));
                    $orderLine->appendChild($domtree->createElement('unitid',''));
                    $orderLine->appendChild($domtree->createElement('price',$_product->getCost()));
                    $orderLine->appendChild($domtree->createElement('priceunit',''));
                    $orderLine->appendChild($domtree->createElement('discount',''));
                    $orderLine->appendChild($domtree->createElement('discountamount',''));

                    $orderLine->appendChild($domtree->createElement('amount',$_product->getCost()));

                    $orderLine->appendChild($domtree->createElement('vatcodeid',''));
                    $orderLine->appendChild($domtree->createElement('vatamount',''));
                    $orderLine->appendChild($domtree->createElement('taxamount',''));
                    $orderLine->appendChild($domtree->createElement('partreceived',''));
                    $orderLine->appendChild($domtree->createElement('partinvoiced',''));
                    $orderLine->appendChild($domtree->createElement('intrastatcodeid',''));
                    $orderLine->appendChild($domtree->createElement('employeeid',''));
                    $orderLine->appendChild($domtree->createElement('orderdate_orig',''));
                    $orderLine->appendChild($domtree->createElement('shippingdate_orig',''));
                    $orderLine->appendChild($domtree->createElement('changed',''));
                    $orderLine->appendChild($domtree->createElement('orderdate',$order->getCreatedAt()));
                    $orderLine->appendChild($domtree->createElement('shippingdate',''));
                }
            }
            $domtree->preserveWhiteSpace = false;
            $domtree->formatOutput = true;

            $xmlData = $domtree->saveXML();
            try{
                $dirPath = "test_xml/";
                $fileName = "ord-".$order->getId()."-".$supplier."_".date("d-m-Y-h-i-s").".xml";
                $filePath = $dirPath.$fileName;

            // $this->sftp->open(array('host' => '54.215.246.241','username' => 'ztsl0065','password' => 'FQsGyr0'));
            // $this->sftp->write($filePath, $xmlData);
            // $this->sftp->close();
             switch($supplier)
            {
                case 'remmer': $this->sftp->open(array('host' => 'kopenhagen.zehntech.net','username' => 'remmer','password' => 'H68BZ3mB4SGU'));
                     $dirPath = "export/";
                     $filePath = $dirPath.$fileName;
                    $this->sftp->write($filePath, $xmlData);
                    $this->sftp->close();
                    break;
                case 'despec':$this->sftp->open(array('host' => 'kopenhagen.zehntech.net','username' => 'despec','password' => 'dypsqtwumclm'));
                    $dirPath = "export/";
                    $filePath = $dirPath.$fileName;
                    $this->sftp->write($filePath, $xmlData);
                    $this->sftp->close();
                    break;
                case 'mmd': $filePath = 'test_xml/'.$fileName;
                    $this->sftp->open(array('host' => 'kopenhagen.zehntech.net','username' => 'ztsl0065','password' => 'FQsGyr0'));
                    $this->sftp->write($filePath, $xmlData);
                    $this->sftp->close();
                    break;
                case 'also': $filePath = 'test_xml/'.$fileName;
                    $this->sftp->open(array('host' => 'kopenhagen.zehntech.net','username' => 'ztsl0065','password' => 'FQsGyr0'));
                    $this->sftp->write($filePath, $xmlData);
                    $this->sftp->close();
                    break; 
                default : $filePath = 'test_xml/'.$fileName;
                    $this->sftp->open(array('host' => 'kopenhagen.zehntech.net','username' => 'ztsl0065','password' => 'FQsGyr0'));
                    $this->sftp->write($filePath, $xmlData);
                    $this->sftp->close();
                    break;
            }

            }catch(\Exception $e){
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/order_upload_fail.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $message = "can not get connection and upload file";
                $logger->info($message."of supplier ".$supplier.", order no.".$order->getId());
                $_dirPath = $this->dir->getPath('media');
                $failedFile = $_dirPath.'/Order_Shipment/UploadFail/'.$fileName;
                $domtree->save($failedFile);
            }
            
        }
        $order->save();
       
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $this->messageManager->addSuccess(__("Order have sended to the suppliers"));
        return $resultRedirect;
    }

    protected function getOptionIdByLabel($optionLabel)
    {
        $attributeCode = 'supplier';
        $_product = $this->_productFactory->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }
    public function getCollection($supplierId)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
        ->addAttributeToFilter('supplier',$supplierId);
        return $collection;
    }

    protected function getProductByOem($collection,$oem)
    {
        foreach ($collection as $key => $product) {
            if($product->getOem()===$oem)
                return $product;
        }
    }


    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Zehntech_SendOrderData::send');
    }

}