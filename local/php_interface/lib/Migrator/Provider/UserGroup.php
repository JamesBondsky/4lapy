<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\GroupTable;
use Bitrix\Main\Type\DateTime;

class UserGroup extends ProviderAbstract
{
    /**
     * @inheritdoc
     */
    public function getMap() : array
    {
        static $map;

        if (!$map) {
            $map = array_diff(array_keys(array_filter(GroupTable::getMap(), $this->getScalarEntityMapFilter())),
                              [$this->entity->getPrimary()]);
    
            $map = array_combine($map, $map);
        }

        return $map;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \RuntimeException
     */
    public function prepareData(array $data) : array
    {
        try {
            $data['TIMESTAMP_X'] = new DateTime($data['TIMESTAMP_X']);
        } catch (\Exception $e) {}
        
        return parent::prepareData($data);
    }
}
