<?php

namespace FourPaws\Migrator\Entity;

class NewsEntity extends IblockEntity
{
    public function setDefaults()
    {
        /**
         * У нас нет значений по умолчанию для этой сущности
         */
        return;
    }
    
    public function addItem(string $primary, array $data) : Result
    {
        parent::addItem($primary, $data);
    }

    public function updateItem(string $primary, array $data) : Result
    {
        parent::updateItem($primary, $data);
    }
}