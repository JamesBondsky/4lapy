<?php

namespace FourPaws\External\Manzana\Model;

final class Parameter
{
    const FIELD_NAME  = 'Name';
    
    const FIELD_VALUE = 'Value';
    
    private $name;
    
    private $value;
    
    public function __construct(string $name, string $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }
    
    /**
     * @return array
     */
    public function getParameter() : array
    {
        return [
            self::FIELD_NAME  => $this->name,
            self::FIELD_VALUE => $this->value,
        ];
    }
}
