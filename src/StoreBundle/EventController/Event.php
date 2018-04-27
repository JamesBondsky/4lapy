<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\StoreBundle\EventController;

use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\ObjectNotFoundException;
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
            /** @var LocationService $locationService */
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $fields['UF_REGION'] = $locationService->findLocationRegion($fields['UF_LOCATION'])['CODE'] ?? '';
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
            /** @var LocationService $locationService */
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $fields['UF_REGION'] = $locationService->findLocationRegion($fields['UF_LOCATION'])['CODE'] ?? '';
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
            $locationService = Application::getInstance()->getContainer()->get('location.service');
            $result->modifyFields(['UF_REGION' => $locationService->findLocationRegion($fields['UF_LOCATION'])['CODE'] ?? '']);
            $event->addResult($result);
        }
    }
}
