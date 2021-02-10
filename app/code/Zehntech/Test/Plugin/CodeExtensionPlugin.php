<?php

namespace Zehntech\Test\Plugin;
use \Magento\InventoryApi\Api\Data\SourceItemExtensionFactory;
use \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CodeExtensionPlugin
{
	protected $extensionFactory;
	private $sourceItemRepository;
	private $sourceRepository;
	private $searchCriteriaBuilder;

	public function __construct(SourceItemExtensionFactory $extensionFactory,
		SourceItemRepositoryInterface $sourceItemRepository,
    	SourceRepositoryInterface $sourceRepository,
    	SearchCriteriaBuilder $searchCriteriaBuilder
    )
	{
		$this->extensionFactory = $extensionFactory;
		$this->sourceItemRepository = $sourceItemRepository;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
	}

	public function afterExecute(GetSourceItemsDataBySku $getClass, $result,string $sku)
	{
		$sourceItemsData = [];
		$searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        $sourcesCache = [];
        foreach ($sourceItems as $sourceItem) {
            $sourceCode = $sourceItem->getSourceCode();
            if (!isset($sourcesCache[$sourceCode])) {
                $sourcesCache[$sourceCode] = $this->sourceRepository->get($sourceCode);
            }

            $source = $sourcesCache[$sourceCode];

            $sourceItemsData[] = [
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                SourceItemInterface::STATUS => $sourceItem->getStatus(),
                SourceInterface::NAME => $source->getName(),
                'source_status' => $source->isEnabled(),
                'cost' => $sourceItem->getCost(),
            ];
        }
        return $sourceItemsData;		
	}

}