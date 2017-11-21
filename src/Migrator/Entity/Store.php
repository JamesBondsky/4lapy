<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Catalog\StoreTable;
use Exception;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

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
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return AddResult
     * @throws AddException
     * @throws Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $result = StoreTable::add($data);

        if ($result->isSuccess() && !MapTable::addEntity($this->entity, $primary, $result->getId())->isSuccess()) {
            throw new AddException('Error: add entity was broken');
        }
        
        return new AddResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        $result = StoreTable::update($primary, $data);
        
        return new UpdateResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @inheritdoc
     *
     * У нас нет складов по умолчанию
     */
    public function setDefaults() : array
    {
        return [];
    }
    
    /**
     * @inheritdoc
     *
     * @throws UpdateException
     * @throws Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $result = StoreTable::update($primary, [$field => $value]);
        
        if ($result->isSuccess()) {
            return new UpdateResult(true, $result->getId());
        }
        
        $errors = $result->getErrorMessages();
        
        throw new UpdateException(sprintf('Update field with primary %s error: %s', $primary, $errors));
    }
}
