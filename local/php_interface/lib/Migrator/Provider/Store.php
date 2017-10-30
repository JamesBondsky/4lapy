<?php

namespace FourPaws\Migrator\Provider;

/**
 * Class Store
 *
 * @package FourPaws\Migrator\Provider
 */
class Store extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        return [
            'NAME' => 'NAME',
            'PROPERTY_code' => 'XML_ID',
            'SORT' => 'SORT',
            'PROPERTY_phone' => 'PHONE',
            'PROPERTY_address' => 'ADDRESS',
            'PROPERTY_work_time' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
            '' => '',
        ];
    }
}
