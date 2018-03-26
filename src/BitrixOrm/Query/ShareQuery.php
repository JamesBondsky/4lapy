<?php

namespace FourPaws\BitrixOrm\Query;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Collection\ShareCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/** @noinspection LongInheritanceChainInspection
 *
 * Class ShareQuery
 *
 * @package FourPaws\BitrixOrm\Query
 */
class ShareQuery extends IblockElementQuery
{
    /**
     * @return array
     */
    public static function getActiveAccessableElementsFilter(): array
    {
        $array = parent::getActiveAccessableElementsFilter();
        $array['SECTION_GLOBAL_ACTIVE'] = 'Y';
        return $array;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
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
            'XML_ID',
            'CODE',
            'SORT',
            'DETAIL_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'PROPERTY_SHARE_TYPE',
            'PROPERTY_TYPE',
            'PROPERTY_ONLY_MP',
            'PROPERTY_SHORT_URL',
            'PROPERTY_OLD_URL',
            'PROPERTY_PRODUCTS',
            'PROPERTY_LABEL',
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     *
     * @throws IblockNotFoundException
     */
    public function getBaseFilter(): array
    {
        return [
            'IBLOCK_ID'        => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
            'PROPERTY_ONLY_MP' => [false, 0],
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     * @return ShareCollection|CollectionBase
     */
    public function exec(): CollectionBase
    {
        return new ShareCollection($this->doExec());
    }
}
