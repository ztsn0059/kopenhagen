<?php

namespace Zehntech\SendOrderData\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionServiceInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryShipping\Model\GetItemsToDeductFromShipment;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;
use Magento\InventoryShipping\Model\SourceDeductionRequestFromShipmentFactory;
use Magento\InventorySourceDeductionApi\Model\SourceDeductionRequestInterface;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class GenerateOrderXml implements ObserverInterface
{

        public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetItemsToDeductFromShipment $getItemsToDeductFromShipment,
        SourceDeductionRequestFromShipmentFactory $sourceDeductionRequestFromShipmentFactory,
        SourceDeductionServiceInterface $sourceDeductionService,
        ItemToSellInterfaceFactory $itemsToSellFactory,
        PlaceReservationsForSalesEventInterface $placeReservationsForSalesEvent,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $_productFact,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        DirectoryList $dir
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getItemsToDeductFromShipment = $getItemsToDeductFromShipment;
        $this->sourceDeductionRequestFromShipmentFactory = $sourceDeductionRequestFromShipmentFactory;
        $this->sourceDeductionService = $sourceDeductionService;
        $this->itemsToSellFactory = $itemsToSellFactory;
        $this->placeReservationsForSalesEvent = $placeReservationsForSalesEvent;
        $this->productRepository = $productRepository;
        $this->productCollection = $productCollectionFactory;
        $this->_productFact = $_productFact;   
        $this->sftp = $sftp;  
        $this->dir = $dir;   
    }


	 public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $domtree = new \DOMDocument('1.0');
        $orderRequest = $domtree->createElement("xmldata");
        $orderRequest = $domtree->appendChild($orderRequest);
     
        // customer details
        $orderHeader = $domtree->createElement("purchaseorder");
        $orderHeader = $orderRequest->appendChild($orderHeader);


    	$shipment = $observer->getEvent()->getShipment();
        $shipmentItems = $this->getItemsToDeductFromShipment->execute($shipment);
        if (!empty($shipment->getExtensionAttributes())
            && !empty($shipment->getExtensionAttributes()->getSourceCode())) {
            $source = $shipment->getExtensionAttributes()->getSourceCode();
        } elseif ($this->isSingleSourceMode->execute()) {
            $source = $this->defaultSourceProvider->getCode();
        }
        $supplierId = $this->getOptionIdByLabel($source);
        $order = $shipment->getOrder();

        // print_r($order->getPayment()->getMethod());
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
        $orderHeader->appendChild($domtree->createElement('requisitionno',$shipment->getId()));
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
        $orderHeader->appendChild($domtree->createElement('shippingdate',$shipment->getCreatedAt()));
        

        
        foreach ($shipmentItems as $key => $item) {
            $sku = $item->getSku();
            $product = $this->productRepository->get($sku);
            $oem = $product->getOem();
            $collection = $this->productCollection->create();
            $collection->addAttributeToSelect('*')->addAttributeToFilter('supplier',array('eq'=>$supplierId));
            $collection->addAttributeToFilter('oem',$oem);
            $product = $collection->getFirstItem(); 
            $orderLine = $domtree->createElement("purchaselines");
            $orderRequest->appendChild($orderLine);

            $orderLine->appendChild($domtree->createElement('recid',$key+1));

            $orderLine->appendChild($domtree->createElement('purchaseid',$order->getId()));

            $orderLine->appendChild($domtree->createElement('linenumber',''));

            $orderLine->appendChild($domtree->createElement('productid',$product->getSku()));

            $orderLine->appendChild($domtree->createElement('locationid',''));
            $orderLine->appendChild($domtree->createElement('description',''));
            $orderLine->appendChild($domtree->createElement('boxes',''));

            $orderLine->appendChild($domtree->createElement('quantity',$item->getQty()));

            $orderLine->appendChild($domtree->createElement('partreceive',''));
            $orderLine->appendChild($domtree->createElement('unitid',''));
            $orderLine->appendChild($domtree->createElement('price',$product->getCost()));
            $orderLine->appendChild($domtree->createElement('priceunit',''));
            $orderLine->appendChild($domtree->createElement('discount',''));
            $orderLine->appendChild($domtree->createElement('discountamount',''));

            $orderLine->appendChild($domtree->createElement('amount',$product->getCost()));

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
            $orderLine->appendChild($domtree->createElement('shippingdate',$shipment->getCreatedAt()));



        }
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        $data = $domtree->saveXML();
        try{
            $dirPath = "test_xml/";
            $fileName = $source."_".$shipment->getId().".xml";
            $filePath = $dirPath.$fileName;

        $this->sftp->open(array('host' => '54.215.246.241','username' => 'ztsl0065','password' => 'FQsGyr0'));
        $this->sftp->write($filePath, $data);
        $this->sftp->close();
        }catch(\Exception $e){
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/order_upload_fail.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $message = "can not get connection and upload file";
            $logger->info($message." for the shiment no. ".$shipment->getId().", order no.".$order->getId());
            $_dirPath = $this->dir->getPath('media');
            $failedFile = $_dirPath.'/Order_Shipment/UploadFail/'.$fileName;
            $domtree->save($failedFile);
        }
    }


    public function getOptionIdByLabel($optionLabel)
    {
        $attributeCode = 'supplier';
        $_product = $this->_productFact->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }
}
