<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\DeliveryBundle\Entity\CalculationResult;


use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;

/**
 * Interface PickupResultInterface
 * @package FourPaws\DeliveryBundle\Entity\CalculationResult
 */
interface PickupResultInterface extends CalculationResultInterface
{
    /**
     * @return StoreCollection
     */
    public function getBestShops(): StoreCollection;

    /**
     * @return Store
     */
    public function getSelectedShop(): Store;

    /**
     * @param Store $selectedStore
     *
     * @return PickupResultInterface
     */
    public function setSelectedShop(Store $selectedStore): PickupResultInterface;
}
