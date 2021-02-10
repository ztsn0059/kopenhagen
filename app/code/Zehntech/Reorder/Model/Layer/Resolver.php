<?php

namespace Zehntech\Reorder\Model\Layer;

class Resolver extends \Magento\Catalog\Model\Layer\Resolver
{
    public function get()
    {
        if (!isset($this->layer)) {
            $this->layer = $this->objectManager->create($this->layersPool['ordered']);
        }
        return $this->layer;
    }
}
