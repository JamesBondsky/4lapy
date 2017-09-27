<?php

namespace FourPaws\BitrixIblockORM\Query;

use CDBResult;
use CIBlockSection;

abstract class IblockSectionQuery extends QueryBase
{
    protected $countElements = false;

    /**
     * @inheritdoc
     */
    public function doExec(): CDBResult
    {
        $arNavStartParams = $this->getNav();
        if (is_array($arNavStartParams) && empty($arNavStartParams)) {
            $arNavStartParams = false;
        }

        return CIBlockSection::GetList(
            $this->getOrder(),
            $this->getFilterWithBase(),
            $this->isCountElements(),
            $this->getSelectWithBase(),
            $arNavStartParams
        );
    }

    /**
     * @return bool
     */
    public function isCountElements(): bool
    {
        return $this->countElements;
    }

    /**
     * @param bool $countElements
     *
     * @return $this
     */
    public function withCountElements(bool $countElements)
    {
        $this->countElements = $countElements;

        return $this;
    }



}
