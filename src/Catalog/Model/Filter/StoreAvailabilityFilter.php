<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Collection\VariantCollection;
use FourPaws\Catalog\Model\Filter\Abstraction\FilterBase;

class StoreAvailabilityFilter extends FilterBase
{
    public static $filterCode = 'AvailableStores';

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return self::$filterCode;
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
        return 'availableStores.keyword';
    }

    /**
     * @return VariantCollection
     */
    public function doGetAllVariants(): VariantCollection
    {
        return new VariantCollection();
    }
}
