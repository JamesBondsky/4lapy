<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

class PackageTypeFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.packagetype';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PackageType';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'KIND_OF_PACKING';
    }

    public function getPath(): string
    {
        return 'offers';
    }

    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_KIND_OF_PACKING';
    }
}
