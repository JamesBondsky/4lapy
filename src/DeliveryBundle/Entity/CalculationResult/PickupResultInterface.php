<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;


use FourPaws\StoreBundle\Collection\StoreCollection;

/**
 * Interface PickupResultInterface
 * @package FourPaws\DeliveryBundle\Entity\CalculationResult
 */
interface PickupResultInterface extends CalculationResultInterface
{
    public function getBestShops(): StoreCollection;
}
