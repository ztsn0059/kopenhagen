<?php

namespace Zehntech\InventoryCost\Plugin;
use \Magento\InventoryApi\Api\Data\SourceItemExtensionFactory;
use \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory; 
use Magento\Catalog\Api\ProductRepositoryInterface; 
class CostExtensionPlugin
{
    protected $extensionFactory;
    private $sourceItemRepository;
    private $sourceRepository;
    private $searchCriteriaBuilder;

    public function __construct(SourceItemExtensionFactory $extensionFactory,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->extensionFactory = $extensionFactory;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCollection = $productCollectionFactory;
        $this->productRepository = $productRepository;
    }

    public function afterExecute(GetSourceItemsDataBySku $getClass, $result,string $sku)
    {
        $product = $this->productRepository->get($sku);
        $attr = $product->getResource()->getAttribute('supplier');
        $oem = $product->getOem();
        $sourceItemsData = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourcesCache = [];
        $sourceData = $this->getCostBySupplierCollection($oem);

        foreach ($sourceItems as $sourceItem) {
            $sourceCode = $sourceItem->getSourceCode();
            if (!isset($sourcesCache[$sourceCode])) {
                $sourcesCache[$sourceCode] = $this->sourceRepository->get($sourceCode);
            }
            $source = $sourcesCache[$sourceCode];
            if(sizeof($sourceData)>1){
                $supplierId = $attr->getSource()->getOptionId($sourceItem->getSourceCode());
                if($supplierId){
                    foreach ($sourceData as $key => $_source) {
                        if($_source['supplierId']==$supplierId)
                            $cost = $_source['cost'];
                    }
                }
                else
                    $cost=0;
            }
            else
                $cost = $sourceData[0]['cost'];
            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::STATUS => $sourceItem->getStatus(),
                SourceInterface::NAME => $source->getName(),
                'source_status' => $source->isEnabled(),
                'cost' => $cost,
            ];
            $cost = 0;
        }
        return $sourceItemsData;        
    }

    public function getCostBySupplierCollection($oem)
    {
        $sourceData = [];
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('oem',$oem);
        foreach ($collection as $key => $_product) {
            $source = [];
            $source['supplierId'] = $_product->getSupplier();
            $source['cost'] = $_product->getCost() ? $_product->getCost():$_product->getPrice();
            $sourceData[] =  $source;
        }
        return $sourceData;
    }

}