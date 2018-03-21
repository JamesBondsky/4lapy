<?php

namespace FourPaws\PersonalBundle\EventController;

use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\PersonalBundle\EventController
 */
class Event implements ServiceHandlerInterface
{
    /**
     * @var EventManager
     */
    protected static $eventManager;

    /**
     * @param \Bitrix\Main\EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        /** очистка кеша  */
        $entity = ['Address', 'Comments', 'Pet', 'Referral'];
        foreach ($entity as $prefix) {
            /** @todo при обновлении может не быть нужных полей, скорее всего придется их перетягивать - только вот сущность не получить - придется плодить милион событий */
            self::initHandler($prefix.'OnBeforeUpdate', [static::class, 'clearCache']);
            self::initHandler($prefix.'OnAfterAdd', [static::class, 'clearCache']);

            self::initHandler($prefix.'OnBeforeDelete', [static::class, 'clearCacheDelete']);
        }
    }

    /**
     *
     *
     * @param string   $eventName
     * @param callable $callback
     * @param string   $module
     *
     */
    public static function initHandler(string $eventName, callable $callback, string $module = 'highloadblock'): void
    {
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    public function clearCache(BitrixEvent $event): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            $id = $event->getParameter('id');
            $fields = $event->getParameter('fields');
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('highloadblock:item:' . $id);
            if (isset($fields['UF_USER_ID'])) {
                $tagCache->clearByTag('highloadblock:field:user:' . $fields['UF_USER_ID']);
            }
        }
    }

    public function clearCacheDelete(BitrixEvent $event): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            $id = $event->getParameter('id');
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('highloadblock:item:' . $id);

            /** @todo сброс кеша по полям, перевести на before и сделать получение*/
        }
    }
}
