<?php

namespace Zehntech\SendOrderData\Block\Adminhtml\Order;

// use Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdSkuAndQty;

class SendDetails extends \Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $messageHelper,$checkoutHelper, $data);
        $this->sourceData = $sourceData;
        $this->collectionFactory = $collectionFactory;
    }

	public function getResult()
	{
		$item = $this->_getData('item');
		$suppliers = [];
		$products = $this->getSuppliersProduct($item->getProduct()->getOem());
		// $supplierData = $this->sourceData->execute($item->getSku());
		// foreach ($supplierData as $key => $supplier) {
		// 	if($supplier['source_code']!=='default'){
		// 		$temp['code'] = $supplier['source_code'];
		// 		$temp['name'] = $supplier['name'];
		// 		$temp['qty'] = $supplier['quantity'];
		// 		$temp['cost'] = $supplier['cost'];
		// 		$suppliers[] = $temp;
		// 	}
		// }
		foreach ($products as $key => $product) {
			$supplierData = $this->sourceData->execute($product->getSku());
			foreach ($supplierData as $key => $supplier) {
				if($supplier['source_code']=='default'){
					$qty = $supplier['quantity'];
				}
			}
			if($item->getQtyOrdered() <= $qty) {
				$attribute = $product->getResource()->getAttribute('supplier');
				if ($attribute->usesSource()) {
			       $optionText = $attribute->getSource()->getOptionText($product->getSupplier());
				}
				$temp['code'] = strtolower($optionText);
				$temp['name'] = $product->getName();
				$temp['qty'] = $qty;
				$temp['cost'] = $product->getCost();
				$suppliers[] = $temp;
			}
		}
		return $suppliers;
	}

	public function getSuppliersProduct($oem)
	{
		$collection = $this->collectionFactory->create();
		$collection->addAttributeToSelect('*')->addAttributeToFilter('oem',$oem);
		return $collection;
	}

}