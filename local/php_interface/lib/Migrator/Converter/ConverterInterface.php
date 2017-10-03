<?php

namespace FourPaws\Migrator\Converter;

interface ConverterInterface
{
    public function __construct(string $fieldName);
    
    /**
     * @param array $data
     *
     * @return array
     */
    public function convert(array $data) : array;
}