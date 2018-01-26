<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PetAgeFilter extends ReferenceFilterBase
{
    public function getFilterCode(): string
    {
        return 'PetAge';
    }

    public function getPropCode(): string
    {
        return 'PET_AGE';
    }

    public function getRuleCode(): string
    {
        return 'PROPERTY_PET_AGE';
    }

    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.petage';
    }
}
