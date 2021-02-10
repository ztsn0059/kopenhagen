<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace Zehntech\OnePageCheckout\Plugin;


class ItemImage
{

    public function afterGetItemImages(\Mageplaza\Osc\Helper\Item $_item, $result, $item)
    {

        $product = $item->getProduct();
        $imgUrl = $product->getImageurl();
        if($imgUrl)
        {
            $imgUrl = is_numeric(strpos($imgUrl,'http')) ? $imgUrl : '//' . $imgUrl;
            $result['src'] = $imgUrl;
            return $result;
        }
        return $result;
    }


}