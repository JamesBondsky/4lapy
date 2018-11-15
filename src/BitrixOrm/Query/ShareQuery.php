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

    public function getProperties(): array
    {
        return [
            'SHARE_TYPE',
            'TYPE',
            'ONLY_MP',
            'SHORT_URL',
            'OLD_URL',
            'PRODUCTS',
            'LABEL',
            'BASKET_RULES',
            'JSON_GROUP_SET',
            'PREMISE_BONUS',
        ];
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
     * @return ShareCollection
     */
    public function exec(): CollectionBase
    {
        return new ShareCollection($this->doExec(), $this->getProperties());
    }
}
