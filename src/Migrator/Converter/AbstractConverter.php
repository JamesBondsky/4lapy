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
    
    /**
     * AbstractConverter constructor.
     *
     * @param string $fieldName
     */
    public function __construct(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function convert(array $data) : array
    {
        return $data;
    }
}