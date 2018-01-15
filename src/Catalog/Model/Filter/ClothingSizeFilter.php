<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

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

    public function getPath(): string
    {
        return 'offers';
    }

    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_CLOTHING_SIZE';
    }
}
