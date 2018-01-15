<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class SeasonFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.season';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Season';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'SEASON_CLOTHES';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_SEASON_CLOTHES';
    }

}
