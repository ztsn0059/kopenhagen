<?php
/**
 * Webkul_Grid Grid Interface.
 *
 * @category    Zehntech
 *
 * @author      Zehntech Technologies Private Limited
 */

namespace Zehntech\PriceMatrix\Api\Data;

interface OlsMatrixInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const MIN_PRICE = 'min_price';
    const MAX_PRICE = 'max _price';
    const MARKUP = 'markup';
    const CREATED_AT = 'created_at';
    const UPDATE_AT = 'update_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set EntityId.
     */
    public function setEntityId($entityId);

    /**
     * Get Min Price.
     *
     * @return varchar
     */
    public function getMinPrice();

    /**
     * Set Min Price.
     */
    public function setMinPrice($minPrice);

    /**
     * Get Max Price.
     *
     * @return varchar
     */
    public function getMaxPrice();

    /**
     * Set Max Price.
     */
    public function setMaxPrice($maxPrice);

    /**
     * Get Markup.
     *
     * @return varchar
     */
    public function getMarkup();

    /**
     * Set Markup.
     */
    public function setMarkup($markup);

    /**
     * Get updated at.
     *
     * @return varchar
     */
    public function getUpdateAt();

    /**
     * Set updated at.
     */
    public function setUpdateAt($updateAt);


    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt);
}