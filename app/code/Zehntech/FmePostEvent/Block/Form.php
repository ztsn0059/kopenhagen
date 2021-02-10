<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\FmePostEvent\Block;

use Magento\Framework\View\Element\Template\Context;


class Form extends \Magento\Framework\View\Element\Template
{

    public function __construct(Context $context, \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, \Magento\Directory\Model\Country $country)
    {
        parent::__construct($context);
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->country = $country;
    }

    public function getFormAction()
    {
        return '/postevents/index/post';
    }

    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of countries in array option
     *
     * @return array
     */
    public function getCountries()
    {
        return $options = $this->getCountryCollection()
            ->setForegroundCountries($this->getTopDestinations())
            ->toOptionArray();
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

}
