<?php

namespace Zehntech\HomeProducts\Block\Brand;

class Search extends \Magento\Framework\View\Element\Template
{
	public function __construct(\Magento\Framework\View\Element\Template\Context $context,
	\Magento\Eav\Model\Config $eavConfig,
	\MGS\Brand\Model\Brand $mgsBrand,
	array $data = []
	)
	{
		parent::__construct($context);
        $this->_eavConfig = $eavConfig;
        $this->mgsBrand = $mgsBrand;
	}

	public function getAllBrand()
	{
		// $attributeCode = "brand";
		// $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		// $options = $attribute->getSource()->getAllOptions();
		// return $options;
		$brandArr = ['brother','canon','dell','dymo','epson','getestner','hewlett packard enterprise','hitachi','hp','kodak','konica minolta','kyocera','lanier','leitz','lexmark','minolta-qms','mita','nashuatec','oki','olivetti','rex rotary','ricoh','samsung','sharp','xerox'];
		$collection = $this->mgsBrand->getCollection()->addFieldToFilter('name',array('in'=>$brandArr));
		return $collection;
	}
}