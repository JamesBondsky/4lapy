<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;
use FourPaws\Catalog\Model\Product;
use FourPaws\Catalog\Model\Variant;

class DeliveryAvailabilityFilter extends FilterBase
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'DeliveryAvailability';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'deliveryAvailability';
    }

    public function doGetAllVariants(): VariantCollection
    {
        $delivery = (new Variant())
            ->withName('Доставка')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_DELIVERY);
        $pickup = (new Variant())
            ->withName('Самовывоз')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_PICKUP);
        $byRequest = (new Variant())
            ->withName('Под заказ')
            ->withAvailable(true)
            ->withValue(Product::AVAILABILITY_BY_REQUEST);
        return new VariantCollection([$delivery, $pickup, $byRequest]);
    }
}
