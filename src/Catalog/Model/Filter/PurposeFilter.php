<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PurposeFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.purpose';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Purpose';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PURPOSE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PURPOSE';
    }

}
