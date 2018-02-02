<?php

namespace FourPaws\SaleBundle\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class OrderDelivery extends Constraint
{
    /**
     * @var string
     */
    public $deliveryIdMessage = 'Выберите способ доставки';

    /**
     * @var string
     */
    public $deliveryDateMessage = 'Выберите дату доставки';

    /**
     * @var string
     */
    public $deliveryIntervalMessage = 'Выберите интервал доставки';

    /**
     * @var string
     */
    public $deliveryPlaceCodeMessage = 'Выберите магазин для самовывоза';

    /**
     * @var string
     */
    public $deliveryDpdTerminalMessage = 'Выберите терминал DPD';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
