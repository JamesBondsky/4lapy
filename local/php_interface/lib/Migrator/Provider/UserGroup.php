<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Type\DateTime;

class UserGroup extends ProviderAbstract
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        $map = array_diff(array_keys(array_filter(GroupTable::getMap(), self::getScalarEntityMapFilter())),
                          [$this->entity->getPrimary()]);
        
        return array_combine($map, $map);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareData(array $data)
    {
        $data['TIMESTAMP_X'] = new DateTime($data['TIMESTAMP_X']);
        
        return parent::prepareData($data);
    }
}