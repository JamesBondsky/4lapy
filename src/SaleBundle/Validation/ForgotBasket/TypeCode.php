<?php

namespace FourPaws\SaleBundle\Validation\ForgotBasket;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TypeCode extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Invalid task type code';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
