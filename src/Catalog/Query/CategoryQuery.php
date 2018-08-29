<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockSectionQuery;
use FourPaws\Catalog\Collection\CategoryCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/**
 * Class CategoryQuery
 *
 * @package FourPaws\Catalog\Query
 */
class CategoryQuery extends IblockSectionQuery
{
    /**
     * Возвращает базовую выборку полей. Например, те поля, которые обязательно нужны для создания сущности.
     *
     * @return array
     */
    public function getBaseSelect(): array
    {
        return [
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'LIST_PAGE_URL',
            'SECTION_PAGE_URL',
            'DEPTH_LEVEL',
            'LEFT_MARGIN',
            'RIGHT_MARGIN',
            'UF_SYMLINK',
            'UF_DISPLAY_NAME',
            'UF_SUFFIX',
            'PICTURE',
            'UF_LANDING',
            'UF_LANDING_BANNER',
            'UF_FAQ_SECTION',
            'UF_FORM_TEMPLATE',
            'UF_DEF_FOR_LANDING',
            'UF_SUB_DOMAIN',
        ];
    }

    /**
     * @return array
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new CategoryCollection($this->doExec());
    }
}
