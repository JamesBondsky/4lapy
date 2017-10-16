<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Sale\Delivery\Services\Table;
use FourPaws\Migrator\Entity\Exceptions\AddException;
use FourPaws\Migrator\Entity\Exceptions\UpdateException;

/**
 * Class Delivery
 *
 * @package FourPaws\Migrator\Entity
 */
class Delivery extends AbstractEntity
{
    /**
     * @inheritdoc
     */
    public function getTimestampByItem(array $item) : string
    {
        return '';
    }
    
    public function setDefaults()
    {
        /**
         * У нас нет доставок по умолчанию
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
        $result = Table::update($primary, $data);
        
        if (!$result->isSuccess()) {
            throw new AddException(sprintf('Delivery #%s update error: %s',
                                           $primary,
                                           implode(', ', $result->getErrorMessages())));
        }
        
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
        $result = Table::add($data);
        
        if (!$result->isSuccess()) {
            throw new AddException(sprintf('Delivery #%s addition error: %s',
                                           $primary,
                                           implode(', ', $result->getErrorMessages())));
        }
        
        MapTable::addEntity($this->entity, $primary, $result->getId());
        
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
        $result = Table::update($primary, [$field => $value]);
        
        if ($result->isSuccess()) {
            return new UpdateResult(true, $result->getId());
        }
        
        $errors = implode(', ', $result->getErrorMessages());
        
        throw new UpdateException(sprintf('Update field with primary %s error: %s.', $primary, $errors));
    }
}
