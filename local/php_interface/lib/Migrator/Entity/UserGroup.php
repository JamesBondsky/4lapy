<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Main\GroupTable;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

class UserGroup extends AbstractEntity
{
    const EXCLUDED_GROUPS = [
        1,
        2,
        6,
    ];
    
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
            6 => 6,
            7 => 6,
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
        if (in_array($primary, self::EXCLUDED_GROUPS)) {
            return new Result(true, $primary);
        }
        
        $result = GroupTable::update($primary, $data);
        
        return new UpdateResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     */
    public function addItem(string $primary, array $data) : Result
    {
        $result = GroupTable::add($data);
        
        if ($result->isSuccess()) {
            if (!MapTable::addEntity($this->entity, $primary, $result->getId())->isSuccess()) {
                throw new AddException("Error: add entity was broken");
            }
        }
        
        return new AddResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $result = GroupTable::update($primary, [$field => $value]);
        
        if ($result->isSuccess()) {
            return new UpdateResult(true, $result->getId());
        }
        
        $errors = $result->getErrorMessages();
        
        throw new UpdateException("Update field with primary {$primary} error: {$errors}");
    }
}