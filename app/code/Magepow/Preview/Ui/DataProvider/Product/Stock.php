<?php

namespace Magepow\Preview\Ui\DataProvider\Product;

class Stock implements \Magento\Ui\DataProvider\AddFieldToCollectionInterface
{
    public function addField(\Magento\Framework\Data\Collection $collection, $field, $alias = null)
    {
        $collection->joinField(
            'stock_status',
            'cataloginventory_stock_status',
            'stock_status',
            'product_id=entity_id',
            null,
            'left'
        );
    }
}