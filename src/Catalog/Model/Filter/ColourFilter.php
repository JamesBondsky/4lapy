<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

class ColourFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.colour';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Colour';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'Colour';
    }

    public function getPath(): string
    {
        return 'offers';
    }

    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_COLOUR';
    }
}
