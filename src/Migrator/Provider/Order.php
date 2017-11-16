<?php

namespace FourPaws\Migrator\Provider;

/**
 * Class Order
 *
 * @package FourPaws\Migrator\Provider
 */
class Order extends Sale
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        return [];
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareData(array $data) : array
    {
        return $data;
    }
}
