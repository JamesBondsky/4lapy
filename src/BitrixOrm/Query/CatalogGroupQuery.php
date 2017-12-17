<?php

namespace FourPaws\BitrixOrm\Query;

use CCatalogGroup;
use CDBResult;
use FourPaws\BitrixOrm\Collection\CatalogGroupCollection;
use FourPaws\BitrixOrm\Collection\CollectionBase;

class CatalogGroupQuery extends QueryBase
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'ID',
            'NAME',
            'BASE',
            'SORT',
            'XML_ID',
            'MODIFIED_BY',
            'CREATED_BY',
            'DATE_CREATE',
            'TIMESTAMP_X',
            'NAME_LANG',
            'CAN_ACCESS',
            'CAN_BUY',
            'CNT',
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
    public function doExec(): CDBResult
    {
        return CCatalogGroup::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $this->getGroup() ?: false,
            $this->getNav() ?: false,
            $this->getSelectWithBase()
        );
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new CatalogGroupCollection($this->doExec());
    }

}
