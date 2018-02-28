<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderBonusPayment extends Constraint
{
    /**
     * @var string
     */
    public $wrongValueMessage = 'Укажите верное кол-во бонусов';

    /**
     * @var string
     */
    public $notAvailableMessage = 'Оплата бонусами невозможна';
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
