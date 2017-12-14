<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PetSizeFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.petsize';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PetSize';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PET_SIZE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PET_SIZE';
    }

}
