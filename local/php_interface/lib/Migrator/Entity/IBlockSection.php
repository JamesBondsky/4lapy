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
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     */
    public function addItem(string $primary, array $data) : Result
    {
        $cIBlockSection = new \CIBlockSection();
        
        $id = $cIBlockSection->Add($data, true, false, false);
        
        if (!$id) {
            throw new AddException("IBlock {$this->getIblockId()} section #{$primary} add error: $cIBlockSection->LAST_ERROR");
        }
        
        MapTable::addEntity($this->entity, $primary, $id);
        
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
        $cIBlockSection = new \CIBlockSection();
        
        if (!$cIBlockSection->Update($primary, $data, true, false, false)) {
            throw new UpdateException("IBlock {$this->getIblockId()} section #{$primary} update error: $cIBlockSection->LAST_ERROR");
        }
        
        return (new UpdateResult(true, $primary));
    }
}