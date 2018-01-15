<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class TradeNameFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.tradename';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'TradeName';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'TRADE_NAME';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_TRADE_NAME';
    }

}
