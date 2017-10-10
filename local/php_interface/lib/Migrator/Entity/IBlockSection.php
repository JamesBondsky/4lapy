<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class IBlockSection
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class IBlockSection extends IBlock
{
    private $isUpdated = false;
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $cIBlockSection = new \CIBlockSection();
        
        $id = $cIBlockSection->Add($data, false, false);
        
        if (!$id) {
            throw new AddException("IBlock {$this->getIblockId()} section #{$primary} add error: $cIBlockSection->LAST_ERROR");
        }
        
        $this->isUpdated = true;
        
        MapTable::addEntity($this->entity, $primary, $id);
        
        return new AddResult(true, $id);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $cIBlockSection = new \CIBlockSection();
        
        if (!$cIBlockSection->Update($primary, $data, false, false)) {
            throw new UpdateException("IBlock {$this->getIblockId()} section #{$primary} update error: $cIBlockSection->LAST_ERROR");
        }
        
        $this->isUpdated = true;
        
        return new UpdateResult(true, $primary);
    }
    
    /**
     * Пересчитываем разделы ТОЛЬКО по окончании миграции
     */
    public function __destruct()
    {
        if ($this->isUpdated) {
            \CIBlockSection::ReSort($this->getIblockId());
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
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        return $this->updateItem($primary, [$field => $value]);
    }
}