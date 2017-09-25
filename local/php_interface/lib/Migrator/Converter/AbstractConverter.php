<?php

namespace FourPaws\Migrator\Converter;

abstract class AbstractConverter implements ConverterInterface
{
    private $fieldName;
    
    /**
     * @return string
     */
    public function getFieldName() : string
    {
        return $this->fieldName;
    }
    
    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    
}