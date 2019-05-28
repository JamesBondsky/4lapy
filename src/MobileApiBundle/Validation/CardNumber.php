<?php

namespace FourPaws\MobileApiBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CardNumber extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Номер карты должен состоять из 13 символов и начинаться с чисел 26 или 27';


    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}