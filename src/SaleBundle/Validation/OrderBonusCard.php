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
    public $cardNotValidMessage = 'Ваша карта не привязана. Пожалуйста, обратитесь на Горячую линию по телефону %s';

    public $cardAlreadyExistsMessage = 'Карта уже привязана';

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
