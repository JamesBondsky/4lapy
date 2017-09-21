<?php

namespace FourPaws\Migrator\Converter;

interface ConverterInterface
{
    public function __construct(string $fieldName);

    public function convert($value);
}