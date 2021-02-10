<?php

namespace Zehntech\HomeProducts\Block;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;

/**
 * @api
 * @since 100.0.2
 */
class BrandDetail extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Brand\Model\BrandFactory $brand,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->brand = $brand;
        parent::__construct($context, $data);
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    public function getBrand()
    {
        $product = $this->getProduct();
        $brandCollection = $this->brand->create();
        return $brandCollection->getCollection()->addFieldToFilter('option_id',$product->getMgsBrand())->getFirstItem();
    }
}