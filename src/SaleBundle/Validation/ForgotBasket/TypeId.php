<?php

namespace FourPaws\SaleBundle\Validation\ForgotBasket;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TypeId extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Invalid task type id';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
