<?php

/**
 * Grid Grid Model.
 * @category  Zehntech
 * @package   Zehntech_PriceMatrix
 * @author    Zehntech
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://zehntech.com)
 */

namespace Zehntech\PriceMatrix\Model;

use Zehntech\PriceMatrix\Api\Data\LargeMatrixInterface;

class LargeMatrix extends \Magento\Framework\Model\AbstractModel implements LargeMatrixInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'zt_large_price_matrix';

    /**
     * @var string
     */
    protected $_cacheTag = 'zt_large_price_matrix';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'zt_large_price_matrix';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Zehntech\PriceMatrix\Model\ResourceModel\LargeMatrix');
    }

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get Min Price.
     *
     * @return varchar
     */
    public function getMinPrice()
    {
        return $this->getData(self::MIN_PRICE);
    }

    /**
     * Set Min Price.
     */
    public function setMinPrice($minPrice)
    {
        return $this->setData(self::MIN_PRICE, $minPrice);
    }

    /**
     * Get Max Price.
     *
     * @return varchar
     */
    public function getMaxPrice()
    {
        return $this->getData(self::MAX_PRICE);
    }

    /**
     * Set Max Price.
     */
    public function setMaxPrice($maxPrice)
    {
        return $this->setData(self::MAX_PRICE, $maxPrice);
    }

    /**
     * Get getContent.
     *
     * @return varchar
     */
    public function getMarkup()
    {
        return $this->getData(self::MARKUP);
    }

    /**
     * Set Content.
     */
    public function setMarkup($markup)
    {
        return $this->setData(self::MARKUP, $markup);
    }


    /**
     * Get UpdateTime.
     *
     * @return varchar
     */
    public function getUpdateAt()
    {
        return $this->getData(self::UPDATE_AT);
    }

    /**
     * Set UpdateTime.
     */
    public function setUpdateAt($updateAt)
    {
        return $this->setData(self::UPDATE_AT, $updateAt);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}