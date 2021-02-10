<?php
/**
 * Zehntech Remmer Interface. *
 * @category    Zehntech *
 * @author  @SumitKumarNamdeo
 */

namespace Zehntech\Despec\Api\Data;

interface DespecFileInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const NAME = 'name';
    const FILE = 'file';
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
     * Get Name.
     *
     * @return varchar
     */
    public function getName();

    /**
     * Set Title.
     */
    public function setName($name);

    /**
     * Get File.
     *
     * @return varchar
     */
    public function getFile();

    /**
     * Set File.
     */
    public function setFile($file);

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