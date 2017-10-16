<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class Status
 *
 * @package FourPaws\Migrator\Entity
 */
class Status extends AbstractEntity
{
    /**
     * @inheritdoc
     */
    public function getTimestampByItem(array $item) : string
    {
        return '';
    }
    
    /**
     * Установим маппинг свойств по-умолчанию
     *
     * @throws \Exception
     */
    public function setDefaults()
    {
        if ($this->checkEntity()) {
            return;
        }
        
        $map = [
            'N' => 'N',
            'F' => 'F',
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
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $result = \CSaleStatus::Update($primary, $data);
        
        return new UpdateResult(false !== $result, $primary);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $result = \CSaleStatus::Add($data);
    
        if (false !== $result) {
            MapTable::addEntity($this->entity, $primary, $primary);
        }
        
        return new AddResult(false !== $result, $primary);
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
        /**
         * Что произошло в случае ошибки мы узнать не сможем.
         */
        if (\CSaleStatus::Update($primary,
                                 [
                                     'ID'   => $primary,
                                     $field => $value,
                                 ])) {
            return new UpdateResult(true, $primary);
        }
        
        throw new UpdateException("Update field with primary {$primary} error.");
    }
}
