<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Zehntech\InvetoryCost\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class SourceItem extends AbstractExtensibleModel implements SourceItemInterface
{
    /**
     * @inheritdoc
     */
    public $this->cost = 'cost';
    protected function _construct()
    {
        $this->_init(SourceItemResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku(?string $sku): void
    {
        $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getSourceCode(): ?string
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceCode(?string $sourceCode): void
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /*new field*/
    /**
     * @inheritdoc
     */
    public function getCost()
    {
        return $this->getData($this->cost);
    }

    /**
     * @inheritdoc
     */
    public function setCost($cost)
    {
        $this->setData($this->cost, $cost);
    }

    /*new field*/


    /**
     * @inheritdoc
     */
    public function getQuantity(): ?float
    {
        return $this->getData(self::QUANTITY) === null ?
            null :
            (float)$this->getData(self::QUANTITY);
    }

    /**
     * @inheritdoc
     */
    public function setQuantity(?float $quantity): void
    {
        $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?int
    {
        return $this->getData(self::STATUS) === null ?
            null :
            (int)$this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(?int $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SourceItemExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceItemInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceItemExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
