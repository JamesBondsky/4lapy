<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Utils;

class News extends IBlockElement
{
    const ENTITY_NAME = 'news';
    
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
    }
    
    /**
     * News constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     *
     * @throws \FourPaws\Migrator\IblockNotFoundException
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            try {
                $iblockId = Utils::getIblockId('publications', 'news');
            } catch (\Exception $e) {
                throw new IblockNotFoundException($e->getMessage());
            }
        }
        
        parent::__construct($entity, $iblockId);
    }
}