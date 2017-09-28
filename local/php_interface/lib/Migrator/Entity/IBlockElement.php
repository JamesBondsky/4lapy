<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class IBlockElement
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class IBlockElement extends IBlock
{
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     */
    public function addItem(string $primary, array $data) : Result
    {
        $cIBlockElement = new \CIBlockElement();
        
        $id = $cIBlockElement->Add($data, false, false, false);
        
        if (!$id) {
            throw new AddException("IBlock {$this->getIblockId()} element #{$primary} add error: $cIBlockElement->LAST_ERROR");
        }
        
        MapTable::addEntity($this->entity, $primary, $id);
        
        $this->setInternalKeys(['sections' => $data['SECTIONS']], $id, $this->entity . '_section');
        
        return (new AddResult(true, $id));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $cIBlockElement = new \CIBlockElement();
        
        if (!$cIBlockElement->Update($primary, $data, false, false, false, false)) {
            throw new UpdateException("IBlock {$this->getIblockId()} element #{$primary} update error: $cIBlockElement->LAST_ERROR");
        } else {
            $this->setInternalKeys(['sections' => $data['SECTIONS']], $primary, $this->entity . '_section');
        }
        
        return (new UpdateResult(true, $primary));
    }
    
    /**
     * Set section list from data
     *
     * @param array  $data
     * @param string $internal
     * @param string $entity
     */
    public function setInternalKeys(array $data, string $internal, string $entity)
    {
        if ($data['sections']) {
            $sectionList = MapTable::getInternalIdListByExternalIdList($data['sections'], $entity);
            
            (new \CIBlockElement())->SetElementSection($internal, $sectionList);
        }
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        if (strpos($field, 'PROPERTY_') === false) {
            return $this->updateField($field, $primary, $value);
        } else {
            return $this->updateProperty(str_replace('PROPERTY_', '', $field), $primary, $value);
        }
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function updateField(string $field, string $primary, $value) : UpdateResult
    {
        $cIblockElement = new \CIBlockElement();
        
        if ($cIblockElement->Update($primary, [$field => $value])) {
            return new UpdateResult(true, $primary);
        }
        
        throw new UpdateException("Update field with primary {$primary} error: {$cIblockElement->LAST_ERROR}");
    }
    
    /**
     * @param string $property
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     */
    public function updateProperty(string $property, string $primary, $value) : UpdateResult
    {
        (new \CIBlockElement())->SetPropertyValues($primary, $this->getIblockId(), [$property => $value]);
        
        /**
         * А вот здесь хер что мы отследим, Битрикс ничего не возвращаем. Считаем, что у нас никаких проблем нет.
         */
        return new UpdateResult(true, $primary);
    }
}