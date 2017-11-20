<?php

namespace FourPaws\BitrixOrm\Query;

use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;

class HlbReferenceQuery extends D7QueryBase
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return ['*'];

        return [
            'UF_ID',
            'UF_XML_ID',
            'UF_NAME',
            'UF_SORT',
            'UF_DESCRIPTION',
            'UF_FULL_DESCRIPTION',
            'UF_LINK',
            'UF_DEF',
            'UF_FILE',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new HlbReferenceItemCollection($this->doExec());
    }

}
