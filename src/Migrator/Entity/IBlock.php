<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class IBlock
 *
 * @package FourPaws\Migrator\Entity
 */
abstract class IBlock extends AbstractEntity
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
     * IBlock constructor.
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
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $cIBlockElement = new \CIBlockElement();
        
        $id = $cIBlockElement->Add($data, false, false);
        
        if (!$id) {
            throw new AddException(sprintf('IBlock %s element #%s add error: %s',
                                           $this->getIblockId(),
                                           $primary,
                                           $cIBlockElement->LAST_ERROR));
        }
        
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
        $cIBlockElement = new \CIBlockElement();
        
        if (!$cIBlockElement->Update($primary, $data, false, false, false, false)) {
            throw new UpdateException(sprintf('IBlock %s element #%s update error: %s',
                                              $this->getIblockId(),
                                              $primary,
                                              $cIBlockElement->LAST_ERROR));
        }
        
        return new UpdateResult(true, $primary);
    }
}
