<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PetTypeFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.pettype';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PetType';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PET_TYPE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PET_TYPE';
    }

}
