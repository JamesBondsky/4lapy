<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderAddress extends Constraint
{
    /**
     * @var string
     */
    public $streetMessage = 'Укажите улицу';

    /**
     * @var string
     */
    public $houseMessage = 'Укажите дом';

    /**
     * @var string
     */
    public $addressMessage = 'Адрес не найден';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
