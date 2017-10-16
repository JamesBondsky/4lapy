<?php

namespace FourPaws\Migrator\Entity;

use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class Order
 *
 * @package FourPaws\Migrator\Entity
 */
class Order extends AbstractEntity
{
    public function setDefaults() : array
    {
        return [];
        /**
         * У нас нет заказов по умолчанию
         */
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \Exception
     */
    public function updateItem(string $primary, array $data) : UpdateResult
    {
        
        
        return new UpdateResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\AddResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\AddException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        
        
        if ($result->isSuccess()) {
            throw new AddException("Error: add entity was broken");
        }
        
        return new AddResult($result->isSuccess(), $result->getId());
    }
    
    /**
     * @param string $field
     * @param string $primary
     * @param        $value
     *
     * @return \FourPaws\Migrator\Entity\UpdateResult
     *
     * @throws \FourPaws\Migrator\Entity\Exceptions\UpdateException
     * @throws \Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        
        throw new UpdateException("Update field with primary {$primary} error: {$errors}");
    }
}
