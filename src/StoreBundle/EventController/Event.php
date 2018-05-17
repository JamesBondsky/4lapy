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
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
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
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param EventManager $eventManager
     *
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        static::$eventManager = $eventManager;

        static::initHandler('StoreOnBeforeAdd', 'updateStoreRegionD7');
        static::initHandler('StoreOnBeforeUpdate', 'updateStoreRegionD7');
        static::initHandler('OnCatalogStoreAdd', 'updateStoreRegion', true);
        static::initHandler('OnBeforeCatalogStoreUpdate', 'updateStoreRegion', true);

        static::initHandler('StoreOnAdd', 'resetStoreCache');
        static::initHandler('StoreOnUpdate', 'resetStoreCache');
        static::initHandler('OnCatalogStoreAdd', 'resetStoreCache', true);
        static::initHandler('OnCatalogStoreUpdate', 'resetStoreCache', true);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param string $eventName
     * @param string $method
     * @param string $module
     */
    public static function initHandler(string $eventName, string $method, $d7 = true, string $module = 'catalog'): void
    {
        if ($d7) {
            static::$eventManager->addEventHandler(
                $module,
                $eventName,
                [static::class, $method]
            );
        } else {
            static::$eventManager->addEventHandlerCompatible(
                $module,
                $eventName,
                [static::class, $method]
            );
        }
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
