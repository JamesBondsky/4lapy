<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderPaymentSystem extends Constraint
{
    /**
     * @var string
     */
    public $paymentSystemMessage = 'Выберите способ оплаты';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
