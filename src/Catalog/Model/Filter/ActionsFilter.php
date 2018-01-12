<?php

namespace FourPaws\Catalog\Model\Filter;

use FourPaws\Catalog\Model\Filter\Abstraction\CheckboxFilter;

class ActionsFilter extends CheckboxFilter
{
    /**
     * @inheritdoc
     */
    public function getFilterCode(): string
    {
        return 'Actions';
    }

    /**
     * @inheritdoc
     */
    public function getPropCode(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getRuleCode(): string
    {
        return 'hasActions';
    }

}
