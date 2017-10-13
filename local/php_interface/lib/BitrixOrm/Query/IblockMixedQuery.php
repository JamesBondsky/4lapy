<?php

namespace FourPaws\BitrixOrm\Query;

use CDBResult;
use CIBlockSection;

/**
 * Class IblockMixedQuery
 *
 * @package FourPaws\BitrixOrm\Query
 */
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
