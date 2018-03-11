<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderPropertyVariant extends Constraint
{

    public $message = 'Задано неверное значение';

    /**
     * Код свойства заказа, значения которого мы проверяем
     *
     * @var string
     */
    public $propertyCode = '';

    /**
     * @return array
     */
    public function getRequiredOptions(): array
    {
        return ['propertyCode'];
    }
}
