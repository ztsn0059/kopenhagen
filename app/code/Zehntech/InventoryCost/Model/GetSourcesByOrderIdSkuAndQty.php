<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zehntech\InventoryCost\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrder;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory; 
use Magento\Catalog\Api\ProductRepositoryInterface; 

/**
 * Class GetSourcesByOrderIdSkuAndQty
 */
class GetSourcesByOrderIdSkuAndQty
{
    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var GetInventoryRequestFromOrder
     */
    private $getInventoryRequestFromOrder;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param GetInventoryRequestFromOrder $getInventoryRequestFromOrder
     * @param SourceRepositoryInterface $sourceRepository
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ItemRequestInterfaceFactory $itemRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        GetInventoryRequestFromOrder $getInventoryRequestFromOrder,
        SourceRepositoryInterface $sourceRepository,
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
        $this->getInventoryRequestFromOrder = $getInventoryRequestFromOrder;
        $this->productCollection = $productCollectionFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Get sources by order id sku and qty
     *
     * @param int $orderId
     * @param string $sku
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(int $orderId, string $sku, float $qty): array
    {
        $algorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();

        $product = $this->productRepository->get($sku);
        $attr = $product->getResource()->getAttribute('supplier');
        $oem = $product->getOem();
        $requestItem = $this->itemRequestFactory->create([
            'sku' => $sku,
            'qty' => $qty
        ]);

        $inventoryRequest = $this->getInventoryRequestFromOrder->execute($orderId, [$requestItem]);
        $sourceSelectionResult = $this->sourceSelectionService->execute(
            $inventoryRequest,
            $algorithmCode
        );

        $result = [];
        $sourceData = $this->getCostBySupplierCollection($oem);
        foreach ($sourceSelectionResult->getSourceSelectionItems() as $item) {
            $supplierId = $attr->getSource()->getOptionId($item->getSourceCode());
            if($supplierId){
                foreach ($sourceData as $key => $_source) {
                    if($_source['supplierId']==$supplierId)
                        $cost = $_source['cost'];
                }
            }
            else
                $cost = 0;
            $sourceCode = $item->getSourceCode();
            $result[] = [
                'sourceName' => $this->getSourceName($sourceCode),
                'sourceCode' => $sourceCode,
                'qtyAvailable' => $item->getQtyAvailable(),
                'qtyToDeduct' => $item->getQtyToDeduct(),
                'cost'=> $item->getCost()
            ];
            $cost = 0;
        }

        return $result;
    }

    /**
     * Get source name by code
     *
     * @param string $sourceCode
     * @return mixed
     * @throws NoSuchEntityException
     */
    private function getSourceName(string $sourceCode): string
    {
        if (!isset($this->sources[$sourceCode])) {
            $this->sources[$sourceCode] = $this->sourceRepository->get($sourceCode)->getName();
        }

        return $this->sources[$sourceCode];
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
