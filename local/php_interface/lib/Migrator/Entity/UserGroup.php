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
            $result = MapTable::addEntity($this->entity, $key, $item);
            
            if (!$result->isSuccess()) {
                throw new \Exception("Error: \n" . implode("\n", $result->getErrorMessages()));
            }
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
        $result = GroupTable::update($primary, $data);
        
        return new Result($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : Result
    {
        $result = GroupTable::add($data);
        
        if ($result->isSuccess()) {
            if (!MapTable::addEntity($this->entity, $primary, $result->getId())->isSuccess()) {
                /**
                 * @todo впилить нормальный exception
                 */
                throw new \Exception("Error: errrrrror");
            }
        }
        
        return new Result($result->isSuccess(), $result->getId());
    }
}