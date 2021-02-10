<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\WeltPixel\Block;

class Display extends \Magento\Framework\View\Element\Template {

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \WeltPixel\OwlCarouselSlider\Model\SliderFactory $sliderFactory,\Magento\Store\Model\StoreManagerInterface $storeManager) {
        parent::__construct($context);
        $this->_sliderFactory = $sliderFactory;
        $this->_storeManager = $storeManager;
    }

    public function getBannerSlides() {

        $bannerSlides = $this->_bannerCollectionFactory->create();
        return $bannerSlides->getData();
    }

    public function getSlider()
    {
    	$slider = $this->_sliderFactory->create();
    	$slider->load(1);
    	return $slider;
    }

    public function getUrlPath()
    {
    	return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }



}
