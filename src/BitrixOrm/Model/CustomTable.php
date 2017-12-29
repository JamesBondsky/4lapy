<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrm\Model;

/**
 * Class CustomTable
 *
 * @package FourPaws\BitrixOrm\Model
 */
abstract class CustomTable implements ModelInterface
{
    
    /**
     * ModelInterface constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $field => $value) {
            if ($this->isExists($field)) {
                $this->{$field} = $value;
            }
        }
    }
    
    /**
     * @param string $fieldName
     *
     * @return bool
     */
    protected function isExists(string $fieldName) : bool
    {
        return property_exists($this, $fieldName);
    }
    
    /**
     * @inheritDoc
     */
    public static function createFromPrimary(string $primary) : ModelInterface
    {
        /**
         * @todo Заглушка. Удалить после реализации создания в более конкретных классах.
         */
    }
    
    /**
     * @return array
     */
    public function toArray() : array
    {
        $result = [];
        //TODO Дописать лучше часть про поля
        foreach (get_object_vars($this) as $field => $value) {
            if (!\is_object($value) && $value !== null) {
                $result[$field] = $value;
            }
        }
        
        return $result;
    }
}
