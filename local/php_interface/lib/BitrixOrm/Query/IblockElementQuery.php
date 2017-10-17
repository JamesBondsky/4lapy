<?php

namespace FourPaws\BitrixOrm\Query;

/**
 * Class IblockElementQuery
 *
 * @package FourPaws\BitrixOrm\Query
 */
abstract class IblockElementQuery extends IblockQueryBase
{
    /**
     * @return \CDBResult
     */
    public function doExec() : \CDBResult
    {
        return \CIBlockElement::GetList($this->getOrder(),
                                        $this->getFilterWithBase(),
                                        $this->getGroup() ?: false,
                                        $this->getNav() ?: false,
                                        $this->getSelectWithBase());
    }
}
