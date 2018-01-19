<?php

namespace FourPaws\SaleBundle\OrderStorage;

class Field
{
    protected $code;

    protected $value;

    public function __construct($code, $value)
    {
        $this->code = $code;
        $this->value = $value;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
