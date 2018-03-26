<?php

namespace FourPaws\PersonalBundle\EventController;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\Helpers\TaggedCacheHelper;

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
        $prefix = 'Address';
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        $prefix = 'Pet';
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        $prefix = 'Referral';
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        $prefix = 'Comments';
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);
    }

    /**
     * @param string   $eventName
     * @param callable $callback
     * @param string   $module
     */
    public static function initHandler(string $eventName, callable $callback, string $module = ''): void
    {
        /** для событий хайлоал блоков модуль должен быть пустой - дичь */
        self::$eventManager->addEventHandler(
            $module,
            $eventName,
            $callback
        );
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function AddressClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Address');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        static::HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function AddressClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Address');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     */
    public static function AddressClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function PetClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Pet');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        static::HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function PetClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Pet');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     */
    public static function PetClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function ReferralClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Referral');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        static::HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function ReferralClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Referral');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     */
    public static function ReferralClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function CommentsClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_OBJECT_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Comments');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        static::HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function CommentsClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Comments');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_OBJECT_ID'])) {
            static::HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     */
    public static function CommentsClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_OBJECT_ID'])) {
            static::HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param $id
     */
    protected static function HlItemClearCache($id): void
    {
        TaggedCacheHelper::clearManagedCache([
            'highloadblock:item:' . $id,
        ]);
    }

    /**
     * @param $type
     * @param $value
     */
    protected static function HlFieldClearCache($type, $value): void
    {
        TaggedCacheHelper::clearManagedCache([
            'highloadblock:field:' . $type . ':' . $value,
        ]);
    }
}
