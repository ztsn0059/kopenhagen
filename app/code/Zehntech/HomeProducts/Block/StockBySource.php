<?php

namespace Zehntech\HomeProducts\Block;

class StockBySource extends \Magento\Framework\View\Element\Template
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
	    \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
	    array $data = []
	) {
    	parent::__construct($context);
    	$this->sourceDataBySku = $sourceDataBySku;
        $this->productRepository = $productRepository;
        $this->_stockItemRepository = $stockItemRepository;
         $this->stockRegistry = $stockRegistry;
	}

    public function getSourceStock($product)
    {
        $supplierId = $product->getSupplier();
        $attr = $product->getResource()->getAttribute('supplier');
         if ($attr->usesSource()) {
               $option = $attr->getSource()->getOptionText($supplierId);
         }
        $supplier = strtolower($option);
        $data = $this->sourceDataBySku->execute($product->getSku());

        foreach ($data as $key => $source) {
            if($source['source_code']==$supplier)
                return $source;
        }
    }

    public function getSourceStockBySku($sku)
    {
        $product = $this->productRepository->get($sku);
        $supplierId = $product->getSupplier();
        $attr = $product->getResource()->getAttribute('supplier');
         if ($attr->usesSource()) {
               $option = $attr->getSource()->getOptionText($supplierId);
         }
        $supplier = strtolower($option);
        $data = $this->sourceDataBySku->execute($product->getSku());

        foreach ($data as $key => $source) {
            if($source['source_code']==$supplier)
                return $source;
        }
    }

    public function getStockQty($product) {
        $stock = $this->stockRegistry->getStockItem($product->getId());
        return $stock->getQty();
    }
}