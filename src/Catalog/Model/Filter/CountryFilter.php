<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class CountryFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.country';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Country';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'COUNTRY';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_COUNTRY';
    }

}
