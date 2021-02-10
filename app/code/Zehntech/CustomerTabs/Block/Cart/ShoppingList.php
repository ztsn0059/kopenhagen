<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zehntech\CustomerTabs\Block\Cart;

use Magento\Catalog\Block\Product\ImageBuilder;

class ShoppingList extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Block\Product\ImageBuilder $imgBuilder,
        \Zehntech\HomeProducts\Block\StockBySource $stockSource        
    )
    {
        parent::__construct($context);
        $this->cart = $cart;
        $this->imgBuilder = $imgBuilder;
        $this->stockSource = $stockSource;
    }
    public function getItems()
    {
        $items = $this->cart->getQuote()->getAllItems();
        return $items;
    }

    public function getImageThumbnail($product)
    {
        return $this->imgBuilder->create($product,'cart_page_product_thumbnail');
    }

    public function getSubTotal()
    {
        return $this->cart->getQuote()->getSubtotal();
    }
    public function getTaxAmount()
    {
        $taxAmount = 0;
        $items = $this->getItems();
        foreach ($items as $key => $item) {
            $taxAmount += $item->getTaxAmount();
        }
        return $taxAmount;
    }
    public function getStockStatus($product)
    {
        $stock = $this->stockSource->getSourceStock($product);
        if($stock >= $product->getMinSales())
        {
            return true;
        }
        return false;
    }

}