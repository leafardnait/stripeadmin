<?php

namespace Magiccart\Magicproduct\Ui\DataProvider\Product;

class Featured implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface
{
    public function addField(\Magento\Framework\Data\Collection $collection, $field, $alias = null)
    {
        $collection->addAttributeToFilter('featured', '1');
    }
}