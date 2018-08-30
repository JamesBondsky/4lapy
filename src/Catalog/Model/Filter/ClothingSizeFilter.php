<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

/**
 * Class ClothingSizeFilter
 *
 * @package FourPaws\Catalog\Model\Filter
 */
class ClothingSizeFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.clothingsize';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'ClothingSize';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'CLOTHING_SIZE';
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
        return 'PROPERTY_CLOTHING_SIZE';
    }
}
