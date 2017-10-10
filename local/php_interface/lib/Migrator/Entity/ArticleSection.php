<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Utils;

class ArticleSection extends IBlockSection
{
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
    }
    
    /**
     * ArticleSection constructor.
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
                $iblockId = Utils::getIblockId('publications', 'articles');
            } catch (\Exception $e) {
                throw new IblockNotFoundException($e->getMessage());
            }
        }
        
        parent::__construct($entity, $iblockId);
    }
}