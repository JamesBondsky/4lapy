<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderBonusCard extends Constraint
{
    /**
     * @var string
     */
    public $cardNotValidMessage = 'Карта не привязывается';

    public $cardAlreadyExistsMessage = 'Карта уже привязана';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
