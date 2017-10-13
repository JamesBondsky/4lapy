<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class Delivery
 *
 * @package FourPaws\Migrator\Entity
 */
class Delivery extends AbstractEntity
{
    
    /**
     * Установим маппинг существующих доставок по умолчанию
     *
     * EXTERNAL -> INTERNAL
     *
     * @throws \Exception
     */
    public function setDefaults()
    {
        if ($this->checkEntity()) {
            return;
        }
        
        $map = [
            1 => 1,
        ];
        
        foreach ($map as $key => $item) {
            $result = MapTable::addEntity($this->entity, $key, $item);
            
            if (!$result->isSuccess()) {
                /**
                 * @todo нормлаьное исключение
                 */
                throw new \Exception("Error: \n" . implode("\n", $result->getErrorMessages()));
            }
        }
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        
        
        return new UpdateResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        
        
        if ($result->isSuccess()) {
            throw new AddException("Error: add entity was broken");
        }
        
        return new AddResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        
        throw new UpdateException("Update field with primary {$primary} error: {$errors}");
    }
}