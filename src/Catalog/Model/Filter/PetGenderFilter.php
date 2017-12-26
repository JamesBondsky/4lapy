<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PetGenderFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.petgender';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PetGender';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PET_GENDER';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PET_GENDER';
    }

}
