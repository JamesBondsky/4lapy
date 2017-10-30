<?php

namespace FourPaws\Migrator\Entity;

/**
 * Class Store
 *
 * @package FourPaws\Migrator\Entity
 */
class Store extends AbstractEntity
{
    /**
     * @inheritDoc
     */
    public function getTimestamp() : string
    {
        return 'DATE_MODIFY';
    }
    
    public function addItem(string $primary, array $data) : AddResult
    {
        // TODO: Implement addItem() method.
    }
    
    /**
     * @inheritDoc
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        // TODO: Implement updateItem() method.
    }
    
    /**
     * @inheritdoc
     */
    public function setDefaults() : array
    {
        return [];
    }
    
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        // TODO: Implement setFieldValue() method.
    }
}
