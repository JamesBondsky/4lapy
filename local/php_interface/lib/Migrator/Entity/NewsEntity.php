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
        // TODO: Implement addItem() method.
    }

    public function updateItem(string $primary, array $data) : Result
    {
        // TODO: Implement updateItem() method.
    }
}