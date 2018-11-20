<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class OfferQuery
 *
 * @package FourPaws\Catalog\Query
 */
class OfferQuery extends IblockElementQuery
{
    /** кешируем на неделю  */
    protected const CACHE_TIME_BY_ID = 7 * 60 * 60 * 12 * 2;

    /**
     * @param int $id
     *
     * @return Offer|null
     */
    public static function getById(int $id = 0): ?Offer
    {
        if ($id <= 0) {
            /** @todo вместо null выбивать exception */
            return null;
        }

        $query = new static();
        $getOffer = function () use ($id, $query) {
            $collection = $query->withFilter(['ID' => $id])->exec();

            return !$collection->isEmpty() ? $collection->first() : null;
        };
        $bitrixCache = new BitrixCache();
        $bitrixCache->withId('offer_' . $id);
        $bitrixCache->withTag('catalog:offer:' . $id);
        $bitrixCache->withTag('iblock:item:' . $id);
        $bitrixCache->withTime(static::CACHE_TIME_BY_ID);
        try {
            return $bitrixCache->resultOf($getOffer)['result'];
        } catch (\Exception $e) {
            /** @todo вместо null выбивать exception */
            $logger = LoggerFactory::create('offer');
            $logger->warning('ошибка получения оффера по id - ' . $id . ': ' . $e->getMessage());
            return null;
        }
    }

    public function getProperties():array
    {
        return [
            'CML2_LINK',
            'IS_NEW',
            'IS_HIT',
            'IS_SALE',
            'IS_POPULAR',
            'COLOUR',
            'VOLUME_REFERENCE',
            'VOLUME',
            'CLOTHING_SIZE',
            'IMG',
            'BARCODE',
            'KIND_OF_PACKING',
            'SEASON_YEAR',
            'MULTIPLICITY',
            'REWARD_TYPE',
            'COLOUR_COMBINATION',
            'FLAVOUR_COMBINATION',
            'OLD_URL',
            'COND_VALUE',
            'PRICE_ACTION',
            'COND_FOR_ACTION',
            'BONUS_EXCLUDE',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseSelect(): array
    {
        return [
            'ACTIVE',
            'DATE_ACTIVE_FROM',
            'DATE_ACTIVE_TO',
            'DATE_CREATE',
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'CATALOG_GROUP_2',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getBaseFilter(): array
    {
        return ['IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)];
    }

    /**
     * @inheritdoc
     */
    public function exec(): CollectionBase
    {
        return new OfferCollection($this->doExec());
    }
}
