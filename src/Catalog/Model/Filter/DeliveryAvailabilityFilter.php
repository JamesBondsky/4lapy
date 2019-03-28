<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
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

    /**
     * @return VariantCollection
     * @throws ApplicationCreateException
     */
    public function doGetAllVariants(): VariantCollection
    {
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $code = $deliveryService->getCurrentDeliveryZone();
        $result = new VariantCollection();
        $result->add((new Variant())
            ->withName('Доставка')
            ->withAvailable(true)
            ->withValue($code . '_' . Product::AVAILABILITY_DELIVERY));
        $result->add((new Variant())
            ->withName('Самовывоз')
            ->withAvailable(true)
            ->withValue($code . '_' . Product::AVAILABILITY_PICKUP));
        $result->add((new Variant())
            ->withName('Под заказ')
            ->withAvailable(true)
            ->withValue($code . '_' . Product::AVAILABILITY_BY_REQUEST));
        return $result;
    }
}
