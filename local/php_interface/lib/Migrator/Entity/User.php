<?php

namespace FourPaws\Migrator\Entity;

class User extends AbstractEntity
{
    public function setDefaults()
    {
        /**
         * Нечего связывать по умолчанию
         */
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function updateItem(string $primary, array $data) : Result
    {
    
    }
    
    /**
     * @param string $primary
     * @param array  $data
     *
     * @return \FourPaws\Migrator\Entity\Result
     */
    public function addItem(string $primary, array $data) : Result
    {
    
    }
}