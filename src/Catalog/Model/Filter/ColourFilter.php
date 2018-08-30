<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

/**
 * Class ColourFilter
 *
 * @package FourPaws\Catalog\Model\Filter
 */
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
        return 'COLOUR';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return 'offers';
    }

    /**
     * @return string
     */
    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_COLOUR';
    }
}
