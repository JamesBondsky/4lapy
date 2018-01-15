<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class ManufactureMaterialFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.material';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'ManufactureMaterial';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'MANUFACTURE_MATERIAL';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_MANUFACTURE_MATERIAL';
    }

}
