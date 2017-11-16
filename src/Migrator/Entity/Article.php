<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\IblockNotFoundException;
use FourPaws\Migrator\Utils;

class Article extends IBlockElement
{
    /**
     * @inheritdoc
     */
    public function setDefaults() : array
    {
        return [];
    }
    
    /**
     * Article constructor.
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
