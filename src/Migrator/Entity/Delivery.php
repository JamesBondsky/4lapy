<?php

namespace FourPaws\Migrator\Entity;

use Bitrix\Sale\Delivery\Services\Manager;
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
    const DEFAULT_CONFIGURATION = 'a:1:{s:4:"MAIN";a:3:{s:8:"CURRENCY";s:3:"RUB";s:5:"PRICE";i:0;s:6:"PERIOD";a:3:{s:4:"FROM";i:0;s:2:"TO";i:0;s:4:"TYPE";s:1:"D";}}}';
    
    /**
     * @inheritdoc
     */
    public function getTimestampByItem(array $item) : string
    {
        return '';
    }
    
    public function setDefaults() : array
    {
        return [];
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
        $data['CONFIG'] = unserialize(self::DEFAULT_CONFIGURATION, ['allowed_classes' => false]);
        $result         = Table::update($primary, $data);
        
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
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function addItem(string $primary, array $data) : AddResult
    {
        $data['CONFIG'] = unserialize(self::DEFAULT_CONFIGURATION, ['allowed_classes' => false]);
        $result         = Manager::add($data);
        
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
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function setFieldValue(string $field, string $primary, $value) : UpdateResult
    {
        $result = Manager::update($primary, [$field => $value]);
        
        if ($result->isSuccess()) {
            return new UpdateResult(true, $result->getId());
        }
        
        $errors = implode(', ', $result->getErrorMessages());
        
        throw new UpdateException(sprintf('Update field with primary %s error: %s.', $primary, $errors));
    }
}
