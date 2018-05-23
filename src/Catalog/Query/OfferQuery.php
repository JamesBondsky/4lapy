<?php

namespace FourPaws\Catalog\Query;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\CollectionBase;
use FourPaws\BitrixOrm\Query\IblockElementQuery;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\LocationBundle\LocationService;
use WebArch\BitrixCache\BitrixCache;

class OfferQuery extends IblockElementQuery
{

    /**
     * @param int $id
     *
     * @return Offer|null
     */
    public static function getById(int $id = 0): ?Offer
    {
        /** кешируем на сутки */
        $cacheTime = 24*60*60;
        if($id <= 0){
            return null;
        }
        /** @var LocationService $locationService */
        try {
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $location = $locationService->getCurrentLocation();
        } catch (ApplicationCreateException $e) {
            /** @todo залогировать ошибку */
            return null;
        }
        $query = new static();
        $getOffer = function () use ($id, $query) {
            $query->withFilter(['ID' => $id]);
            $collection = $query->exec();
            /** @todo перед сохранением вызвать все расчетные методы, для сохранения рассчитанного оффера?? */
            return $collection->isEmpty() ? null : $collection->first();
        };
        $bitrixCache = new BitrixCache();
        $bitrixCache->withId('offer_' . $id . '_location_' . $location);
        $bitrixCache->withTag('catalog:offer:' . $id);
        $bitrixCache->withTag('iblock:item:' . $id);
        $bitrixCache->withTime($cacheTime);//пока что кешируем на сутки
        /** @todo увеличить время кеширование до года - если кеш будет корректно отрабатывать */
        try {
            return $bitrixCache->resultOf($getOffer)['result'];
        } catch (\Exception $e) {
            return null;
            /** @todo залогировать ошибку */
        }
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
            'IBLOCK_ID',
            'ID',
            'NAME',
            'XML_ID',
            'CODE',
            'DETAIL_PAGE_URL',
            'SECTION_PAGE_URL',
            'LIST_PAGE_URL',
            'CANONICAL_PAGE_URL',
            'PROPERTY_CML2_LINK',
            'PROPERTY_IS_NEW',
            'PROPERTY_IS_HIT',
            'PROPERTY_IS_SALE',
            'PROPERTY_IS_POPULAR',
            'PROPERTY_COLOUR',
            'PROPERTY_VOLUME_REFERENCE',
            'PROPERTY_VOLUME',
            'PROPERTY_CLOTHING_SIZE',
            'PROPERTY_IMG',
            'PROPERTY_BARCODE',
            'PROPERTY_KIND_OF_PACKING',
            'PROPERTY_SEASON_YEAR',
            'PROPERTY_MULTIPLICITY',
            'PROPERTY_REWARD_TYPE',
            'PROPERTY_COLOUR_COMBINATION',
            'PROPERTY_FLAVOUR_COMBINATION',
            'PROPERTY_OLD_URL',
            'CATALOG_GROUP_2',
            'PROPERTY_COND_VALUE',
            'PROPERTY_PRICE_ACTION',
            'PROPERTY_COND_FOR_ACTION',
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
