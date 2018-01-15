<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class FeedSpecFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.feedspec';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'FeedSpec';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'FEED_SPECIFICATION';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_FEED_SPECIFICATION';
    }

}
