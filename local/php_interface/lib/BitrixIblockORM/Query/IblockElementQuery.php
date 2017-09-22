<?php

namespace FourPaws\BitrixIblockORM\Query;

use CDBResult;
use CIBlockElement;

abstract class IblockElementQuery extends QueryBase
{

    public function doExec(): CDBResult
    {
        $arNavStartParams = $this->getNav();
        if (is_array($arNavStartParams) && empty($arNavStartParams)) {
            $arNavStartParams = false;
        }

        $arGroupBy = $this->getGroup();
        if (is_array($arGroupBy) && empty($arGroupBy)) {
            $arGroupBy = false;
        }

        return CIBlockElement::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $arGroupBy,
            $arNavStartParams,
            $this->getSelectWithBase()
        );
    }

}
