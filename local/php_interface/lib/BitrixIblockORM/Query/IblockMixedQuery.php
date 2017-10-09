<?php

namespace FourPaws\BitrixIblockORM\Query;

use CDBResult;
use CIBlockSection;

abstract class IblockMixedQuery extends IblockSectionQuery
{
    public function doExec(): CDBResult
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return CIBlockSection::GetMixedList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $this->isCountElements(),
            $this->getSelectWithBase()
        );
    }
}
