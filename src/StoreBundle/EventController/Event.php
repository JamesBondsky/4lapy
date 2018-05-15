<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\EventController;

use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SapBundle\Exception\LogicException;
use FourPaws\SapBundle\Exception\UnexpectedValueException;

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

        self::initHandler('StoreOnBeforeAdd', 'updateStoreRegionD7');
        self::initHandler('StoreOnBeforeUpdate', 'updateStoreRegionD7');
        static::initHandler('OnBeforeCatalogStoreAdd', 'addStoreRegion', true);
        static::initHandler('OnBeforeCatalogStoreUpdate', 'updateStoreRegion', true);
    }

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
     * @param $fields
     *
     * @throws ApplicationCreateException
     */
    public static function addStoreRegion(&$fields) {
        if ($fields['UF_LOCATION']) {
            $fields['UF_REGION'] = static::getRegionCode($fields['UF_LOCATION']);
        }
    }

    /**
     * @param $id
     * @param $fields
     *
     * @throws ApplicationCreateException
     */
    public static function updateStoreRegion($id, &$fields) {
        if ($fields['UF_LOCATION']) {
            $fields['UF_REGION'] = static::getRegionCode($fields['UF_LOCATION']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ApplicationCreateException
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public static function updateStoreRegionD7(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_LOCATION'])) {
            $result = new EventResult();
            $result->modifyFields(['UF_REGION' => static::getRegionCode($fields['UF_LOCATION'])]);
            $event->addResult($result);
        }
    }

    /**
     * @param string $location
     * @return array
     * @throws ApplicationCreateException
     */
    protected static function getRegionCode(string $location): array
    {
        $locationService = Application::getInstance()->getContainer()->get('location.service');

        $region = $locationService->findLocationRegion($location)['CODE'] ?? '';
        if ($region === LocationService::LOCATION_CODE_MOSCOW) {
            $region = [$region, LocationService::LOCATION_CODE_MOSCOW_REGION];
        }

        return (array)$region;
    }
}
