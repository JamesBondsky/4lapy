<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\EventController;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Enum\StoreFields;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\SapBundle\EventController
 */
class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $module = 'catalog';
        static::initHandler('StoreOnBeforeAdd', [static::class, 'updateStoreRegionD7'],$module);
        static::initHandler('StoreOnBeforeUpdate', [static::class, 'updateStoreRegionD7'], $module);
        static::initHandlerCompatible('OnCatalogStoreAdd', [static::class,'updateStoreRegion'], $module);
        static::initHandlerCompatible('OnBeforeCatalogStoreUpdate', [static::class,'updateStoreRegion'], $module);

        static::initHandler('StoreOnAdd', [static::class,'resetStoreCache'], $module);
        static::initHandler('StoreOnUpdate', [static::class,'resetStoreCache'], $module);
        static::initHandlerCompatible('OnCatalogStoreAdd', [static::class,'resetStoreCache'], $module);
        static::initHandlerCompatible('OnCatalogStoreUpdate', [static::class,'resetStoreCache'], $module);
    }

    /**
     * @param $id
     * @param $fields
     *
     * @throws ApplicationCreateException
     */
    public static function updateStoreRegion($id, $fields)
    {
        /** @var StoreService $storeService */
        $storeService = Application::getInstance()->getContainer()->get('store.service');
        try {
            $store = $storeService->getStoreById($id);
            $store->setLocation($fields[StoreFields::LOCATION]);
            $storeService->saveStore($store);
        } catch (NotFoundException $e) {
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function updateStoreRegionD7(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');

        $modifiedFields = [];
        if (isset($fields[StoreFields::LOCATION])) {
            $entityFields = StoreTable::getEntity()->getFields();
            if (isset($entityFields[StoreFields::REGION])) {
                $modifiedFields[StoreFields::REGION] = static::getRegionCode($fields[StoreFields::LOCATION]);
            }

            if (isset($entityFields[StoreFields::SUBREGION])) {
                $modifiedFields[StoreFields::SUBREGION] = static::getSubRegionCode($fields[StoreFields::LOCATION]);
            }
        }

        if ($modifiedFields) {
            $result = new EventResult();
            $result->modifyFields($modifiedFields);
            $event->addResult($result);
        }
    }

    public static function resetStoreCache(): void
    {
        TaggedCacheHelper::clearManagedCache(['catalog:store']);
    }

    /**
     * @param string $location
     *
     * @return array
     * @throws ApplicationCreateException
     */
    protected static function getRegionCode(string $location): array
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        $region = $locationService->findLocationRegion($location)['CODE'] ?? '';
        if ($region === LocationService::LOCATION_CODE_MOSCOW) {
            $region = [$region, LocationService::LOCATION_CODE_MOSCOW_REGION];
        } elseif ($region === LocationService::LOCATION_CODE_MOSCOW_REGION) {
            $region = [$region, LocationService::LOCATION_CODE_MOSCOW];
        }

        return (array)$region;
    }

    /**
     * @param string $location
     *
     * @return string
     * @throws ApplicationCreateException
     */
    protected static function getSubRegionCode(string $location): string
    {
        /** @var LocationService $locationService */
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        return $locationService->findLocationSubRegion($location)['CODE'] ?? '';
    }
}
