<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

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
    public $deliveryPlaceCodeMessage = 'Выберите пункт самовывоза';

    /**
     * @var string
     */
    public $deliveryDateExpiredMessage = 'Время сессии истекло. Вы будете перенаправлены на шаг выбора доставки';

    /**
     * @var string
     */
    public $orderSplitMessage = 'Разделение заказа невозможно';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
