<?php

namespace FourPaws\Migrator\Entity;

/**
 * Class IBlockEntity
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class IBlockEntity extends AbstractEntity
{
    private $iblockId = 0;
    
    /**
     * @return int
     */
    public function getIblockId() : int
    {
        return $this->iblockId;
    }
    
    /**
     * @param int $iblockId
     */
    private function setIblockId(int $iblockId)
    {
        $this->iblockId = $iblockId;
    }
    
    /**
     * IBlockEntity constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct(string $entity, int $iblockId)
    {
        $this->setIblockId($iblockId);
        parent::__construct($entity);
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(string $primary, array $data) : Result
    {
        $cIBlockElement = new \CIBlockElement();
        
        $id = $cIBlockElement->Add($data, false, false, false);
        
        if ($id) {
            MapTable::addEntity($this->entity, $primary, $id);
        } else {
            $this->getLogger()
                 ->error("IBlock {$this->getIblockId()} element #{$primary} add error: $cIBlockElement->LAST_ERROR");
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
        $cIBlockElement = new \CIBlockElement();
        
        if (!($success = $cIBlockElement->Update($primary, $data, false, false, false, false))) {
            $this->getLogger()
                 ->error("IBlock {$this->getIblockId()} element #{$primary} update error: $cIBlockElement->LAST_ERROR");
        }

        return (new Result($success, $primary));
    }
}