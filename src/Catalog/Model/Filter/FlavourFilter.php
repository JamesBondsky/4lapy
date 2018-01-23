<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class FlavourFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.flavour';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Flavour';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'FLAVOUR';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_FLAVOUR';
    }

}
