<?php

namespace FourPaws\Migrator\Entity;

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
     */
    public function addItem(string $primary, array $data) : Result
    {
        $cIBlockSection = new \CIBlockSection();
        
        $id = $cIBlockSection->Add($data, true, false, false);
        
        if ($id) {
            MapTable::addEntity($this->entity, $primary, $id);
        } else {
            $this->getLogger()
                 ->error("IBlock {$this->getIblockId()} section #{$primary} add error: $cIBlockSection->LAST_ERROR");
        }
        
        return (new Result($id > 0, $id));
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
        $cIBlockSection = new \CIBlockSection();
        
        if (!($success = $cIBlockSection->Update($primary, $data, true, false, false))) {
            $this->getLogger()
                 ->error("IBlock {$this->getIblockId()} section #{$primary} update error: $cIBlockSection->LAST_ERROR");
        }
        
        return (new Result($success, $primary));
    }
}