<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Utils;

class News extends IBlockElement
{
    const ENTITY_NAME = 'news';
    
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
    
    /**
     * News constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            $iblockId = Utils::getIblockId('publications', 'news');
        }
        
        parent::__construct($entity, $iblockId);
    }
}