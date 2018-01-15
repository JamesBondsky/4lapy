<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterNested;

class VolumeReferenceFilter extends ReferenceFilterNested
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.volume';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Volume';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'VOLUME_REFERENCE';
    }

    public function getPath(): string
    {
        return 'offers';
    }

    public function getNestedRuleCode(): string
    {
        return 'PROPERTY_VOLUME_REFERENCE';
    }
}
