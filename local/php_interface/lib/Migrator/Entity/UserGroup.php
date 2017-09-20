<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\GroupTable;

class UserGroup extends AbstractEntity
{
    /**
     * Установим маппинг существующих групп по умолчанию
     *
     * EXTERNAL -> INTERNAL
     */
    public function setDefaults()
    {
        if ($this->checkEntity()) {
            return;
        }
        
        $map = [
            1 => 1,
            2 => 2,
            6 => 3,
            7 => 4,
        ];

        foreach ($map as $key => $item) {
            MapTable::add([
                              'ENTITY'      => $this->entity,
                              'EXTERNAL_ID' => $key,
                              'INTERNAL_ID' => $item,
                          ]);
        }
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $result = GroupTable::add($data);
        
        if ($result->isSuccess()) {
            MapTable::add([
                              'ENTITY'      => $this->entity,
                              'EXTERNAL_ID' => $primary,
                              'INTERNAL_ID' => $result->getId(),
                          ]);
        }
        
        return new Result($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(string $primary, array $data) : Result
    {
        $result = GroupTable::update($primary, $data);
        
        return new Result($result->isSuccess(), $result->getId());
    }
}