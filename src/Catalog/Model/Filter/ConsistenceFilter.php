<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class ConsistenceFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.consistence';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Consistence';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'CONSISTENCE';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_CONSISTENCE';
    }

}
