<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class OrderProperty
 *
 * @package FourPaws\Migrator\Entity
 */
class OrderProperty extends AbstractEntity
{
    /**
     * @inheritdoc
     */
    public function getTimestampByItem(array $item) : string
    {
        return '';
    }
    
    /**
     * Установим маппинг существующих свойств заказа по умолчанию
     *
     * EXTERNAL -> INTERNAL
     *
     * @return array
     * @throws \Exception
     */
    public function setDefaults() : array
    {
        if ($this->checkEntity()) {
            return [];
        }
        
        $map = [
            5  => 1,
            7  => 2,
            8  => 14,
            10 => 24,
            13 => 18,
            14 => 12,
            16 => 23,
            17 => 5,
            18 => 6,
            19 => 7,
            20 => 8,
            21 => 9,
            22 => 10,
            23 => 11,
            25 => 13,
            26 => 19,
            32 => 15,
            36 => 20,
            40 => 16,
            46 => 22,
            50 => 21,
            53 => 17,
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
        return new UpdateResult(true, $primary);
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
        return new AddResult(true, $primary);
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
        throw new UpdateException('Update field error: it`s mock entity.');
    }
}
