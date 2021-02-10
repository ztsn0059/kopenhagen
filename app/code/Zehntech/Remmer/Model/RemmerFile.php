<?php

/**
 * Zehntech Remmer Interface. *
 * @category    Zehntech *
 * @author  @SumitKumarNamdeo
 */

namespace Zehntech\Remmer\Model;

use Zehntech\Remmer\Api\Data\RemmerFileInterface;

class RemmerFile extends \Magento\Framework\Model\AbstractModel implements RemmerFileInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'remmer_product_list';

    /**
     * @var string
     */
    protected $_cacheTag = 'remmer_product_list';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'remmer_product_list';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Zehntech\Remmer\Model\ResourceModel\RemmerFile');
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
     * Get Title.
     *
     * @return varchar
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set Title.
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get Image.
     *
     * @return varchar
     */
    public function getFile()
    {
        return $this->getData(self::FILE);
    }

    /**
     * Set Max Price.
     */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
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