<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zehntech\InventoryCost\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory; 
use Magento\Catalog\Api\ProductRepositoryInterface; 
/**
 * Return a default response for sorted source algorithms
 */
class GetDefaultSortedSourcesResult
{
    /**
     * @var SourceSelectionItemInterfaceFactory
     */
    private $sourceSelectionItemFactory;

    /**
     * @var SourceSelectionResultInterfaceFactory
     */
    private $sourceSelectionResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * GetDefaultSortedSourcesResult constructor.
     *
     * @param SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory
     * @param SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     */
    public function __construct(
        SourceSelectionItemInterfaceFactory $sourceSelectionItemFactory,
        SourceSelectionResultInterfaceFactory $sourceSelectionResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->sourceSelectionItemFactory = $sourceSelectionItemFactory;
        $this->sourceSelectionResultFactory = $sourceSelectionResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->productCollection = $productCollectionFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Compare float number with some epsilon
     *
     * @param float $floatNumber
     *
     * @return bool
     */
    private function isZero(float $floatNumber): bool
    {
        return $floatNumber < 0.0000001;
    }

    /**
     * Returns source item from specific source by given SKU. Return null if source item is not found
     *
     * @param string $sourceCode
     * @param string $sku
     * @return SourceItemInterface|null
     */
    private function getSourceItemBySourceCodeAndSku(string $sourceCode, string $sku): ?SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItemsResult = $this->sourceItemRepository->getList($searchCriteria);

        return $sourceItemsResult->getTotalCount() > 0 ? current($sourceItemsResult->getItems()) : null;
    }

    /**
     * Generate default result for priority based algorithms
     *
     * @param InventoryRequestInterface $inventoryRequest
     * @param SourceInterface[] $sortedSources
     * @return SourceSelectionResultInterface
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        array $sortedSources
    ): SourceSelectionResultInterface {
        $isShippable = true;
        $sourceItemSelections = [];
        //TODO from performance perspective it's better to switch these foreaches and make the inner one
        //TODO which loops over sources to be outermost and iterate over inventory request items inside
        foreach ($inventoryRequest->getItems() as $item) {
            $itemSku = $item->getSku();
            $qtyToDeliver = $item->getQty();
             $product = $this->productRepository->get($itemSku);
            $attr = $product->getResource()->getAttribute('supplier');
            $oem = $product->getOem();
            $sourceData = $this->getCostBySupplierCollection($oem);
            foreach ($sortedSources as $source) {
                $sourceItem = $this->getSourceItemBySourceCodeAndSku($source->getSourceCode(), $itemSku);
                if (null === $sourceItem) {
                    continue;
                }

                if ($sourceItem->getStatus() !== SourceItemInterface::STATUS_IN_STOCK) {
                    continue;
                }

                $sourceItemQty = $sourceItem->getQuantity();
                $qtyToDeduct = min($sourceItemQty, $qtyToDeliver);
                // $cost = $sourceItem->getCost();
                
                $supplierId = $attr->getSource()->getOptionId($sourceItem->getSourceCode());
                if($supplierId){
                    foreach ($sourceData as $key => $_source) {
                        if($_source['supplierId']==$supplierId)
                            $cost = $_source['cost'];
                    }
                }
                else
                    $cost = 0;
                // check if source has some qty of SKU, so it's possible to take them into account
                if ($this->isZero((float)$sourceItemQty)) {
                    continue;
                }

                $sourceItemSelections[] = $this->sourceSelectionItemFactory->create([
                    'sourceCode' => $sourceItem->getSourceCode(),
                    'sku' => $itemSku,
                    'qtyToDeduct' => $qtyToDeduct,
                    'qtyAvailable' => $sourceItemQty,
                    'cost' => $cost,
                ]);

                $qtyToDeliver -= $qtyToDeduct;
            }

            // if we go through all sources from the stock and there is still some qty to delivery,
            // then it doesn't have enough items to delivery
            if (!$this->isZero($qtyToDeliver)) {
                $isShippable = false;
            }
        }

        return $this->sourceSelectionResultFactory->create(
            [
                'sourceItemSelections' => $sourceItemSelections,
                'isShippable' => $isShippable
            ]
        );
    }

    public function getCostBySupplierCollection($oem)
    {
        $sourceData = [];
        $collection = $this->productCollection->create();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('oem',$oem);
        foreach ($collection as $key => $_product) {
            $source = [];
            $source['supplierId'] = $_product->getSupplier();
            $source['cost'] = $_product->getCost();
            $sourceData[] =  $source;
        }
        return $sourceData;
    }
}
