<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Utils;

class ArticleSection extends IBlockSection
{
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
    
    /**
     * ArticleSection constructor.
     *
     * @param string $entity
     * @param int    $iblockId
     */
    public function __construct($entity, $iblockId = 0)
    {
        if (!$iblockId) {
            $iblockId = Utils::getIblockId('publications', 'articles');
        }
        
        parent::__construct($entity, $iblockId);
    }
}