<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zehntech\InventoryCost\Model;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionItemExtensionInterface;

/**
 * @inheritdoc
 */
class SourceSelectionItem extends AbstractExtensibleModel implements SourceSelectionItemInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var float
     */
    private $qtyToDeduct;

    /**
     * @var float
     */
    private $qtyAvailable;

    /**
     * @var float
     */
    private $cost;



    /**
     * SourceSelectionItem constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param string $sourceCode
     * @param string $sku
     * @param float $qtyToDeduct
     * @param float $qtyAvailable
     * @param float $cost
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        string $sourceCode,
        string $sku,
        float $qtyToDeduct,
        float $qtyAvailable,
        float $cost,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->sourceCode = $sourceCode;
        $this->sku = $sku;
        $this->qtyToDeduct = $qtyToDeduct;
        $this->qtyAvailable = $qtyAvailable;
        $this->cost = $cost;

    }

    /**
     * @inheritdoc
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function getQtyToDeduct(): float
    {
        return $this->qtyToDeduct;
    }

    /**
     * @inheritdoc
     */
    public function getQtyAvailable(): float
    {
        return $this->qtyAvailable;
    }

    /**
     * @inheritdoc
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SourceSelectionItemExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(
                SourceSelectionItemInterface::class
            );
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceSelectionItemExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
