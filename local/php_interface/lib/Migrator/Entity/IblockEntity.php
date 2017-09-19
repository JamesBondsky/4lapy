<?php

namespace FourPaws\Migrator\Entity;

abstract class IblockEntity extends AbstractEntity
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
     * IblockEntity constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct(string $entity, int $iblockId)
    {
        $this->setIblockId($iblockId);
        parent::__construct($entity);
    }

    public function addItem(string $primary, array $data) : Result
    {
        // TODO: Implement addItem() method.
    }

    public function updateItem(string $primary, array $data) : Result
    {
        // TODO: Implement updateItem() method.
    }
}