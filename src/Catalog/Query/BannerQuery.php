<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\BannerCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class BannerQuery extends IblockElementQuery
{
    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'ACTIVE',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'IBLOCK_ID',
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'XML_ID',
            'CODE',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
        ];
    }

    public function getProperties(): array
    {
        return [
            'IMG_TABLET',
            'LINK',
            'BACKGROUND',
            'SECTION',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new BannerCollection($this->doExec());
    }

    /**
     * @param string $type
     * @return $this
     */
    public function withType(string $type) {
        $filter = $this->getBaseFilter();
        $filter['CODE'] = $type;
        $section = \CIBlockSection::getList([], $filter)->fetch();
        $this->filter['SECTION_ID'] = $section['ID'];
        return $this;
    }

}
