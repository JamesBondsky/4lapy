<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Currency extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Укажите верный код валюты';


    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
