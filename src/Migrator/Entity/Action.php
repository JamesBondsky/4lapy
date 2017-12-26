<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Utils;

class Action extends IBlockElement
{
    const ENTITY_NAME = 'action';
    
    public function setDefaults() : array
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return [];
    }
    
    /**
     * News constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     *
     * @throws IblockNotFoundException
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            try {
                $iblockId = Utils::getIblockId('publications', 'shares');
            } catch (\Exception $e) {
                throw new IblockNotFoundException($e->getMessage());
            }
        }
        
        parent::__construct($entity, $iblockId);
    }
}
