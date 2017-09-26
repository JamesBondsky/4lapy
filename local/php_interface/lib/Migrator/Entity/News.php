<?php

namespace FourPaws\Migrator\Entity;

class News extends IBlock
{
    const ENTITY_NAME = 'news';

    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
}