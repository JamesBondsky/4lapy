<?php

namespace FourPaws\BitrixOrm\Query;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\BitrixOrm\Collection\ArticleCollection;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Collection\ShareCollection;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

/** @noinspection LongInheritanceChainInspection
 *
 * Class ArticleQuery
 *
 * @package FourPaws\BitrixOrm\Query
 */
class ArticleQuery extends IblockElementQuery
{
    /**
     * @return array
     */
    public static function getActiveAccessableElementsFilter(): array
    {
        $array = parent::getActiveAccessableElementsFilter();
        unset($array['SECTION_GLOBAL_ACTIVE']);

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
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'SORT',
            'DETAIL_PAGE_URL',
            'PREVIEW_TEXT',
            'PREVIEW_PICTURE',
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
            'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::ARTICLES),
        ];
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @inheritdoc
     * @return ShareCollection
     */
    public function exec(): CollectionBase
    {
        return new ArticleCollection($this->doExec());
    }
}
