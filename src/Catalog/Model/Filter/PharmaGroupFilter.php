<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\ReferenceFilterBase;

class PharmaGroupFilter extends ReferenceFilterBase
{
    /**
     * @inheritdoc
     */
    protected function getHlBlockServiceName(): string
    {
        return 'bx.hlblock.pharmagroup';
    }

    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'PharmaGroup';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return 'PHARMA_GROUP';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'PROPERTY_PHARMA_GROUP';
    }

}
