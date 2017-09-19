<?php

namespace FourPaws\Migrator\Provider;

use Bitrix\Main\UserGroupTable;
use FourPaws\Migrator\Entity\Result;

class UserGroup extends ProviderAbstract
{
    /**
     * @return array
     */
    public function getMap() : array
    {
        $map = array_keys(array_filter(UserGroupTable::getMap(), self::getScalarEntityMapFilter()));
        
        return array_combine($map, $map);
    }
    
    /**
     * @return string
     */
    public function getTimestamp() : string
    {
        return 'TIMESTAMP_X';
    }
    
    /**
     * @return string
     */
    public function getPrimary() : string
    {
        return 'ID';
    }

    public function addItem(array $data) : Result
    {
    
    }
    
    public function updateItem(string $primary, array $data) : Result
    {
        
    }
}