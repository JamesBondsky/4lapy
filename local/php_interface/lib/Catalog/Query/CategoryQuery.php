<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockSectionQuery;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class CategoryQuery extends IblockSectionQuery
{
    public function getBaseSelect(): array
    {
        return [
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'DEPTH_LEVEL',
            'LEFT_MARGIN',
            'RIGHT_MARGIN',
            //TODO Пользовательские свойства? UF_SYMLINK?
        ];
    }

    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)];
    }

    /**
     * @return CollectionBase
     */
    public function exec(): CollectionBase
    {
        return new CategoryCollection($this->doExec());
    }

}
