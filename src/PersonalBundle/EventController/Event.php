<?php

namespace FourPaws\PersonalBundle\EventController;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Bitrix\Main\Application as BitrixApplication;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
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

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function AddressClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        $this->HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Address');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function AddressClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Address');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     */
    public function AddressClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function PetClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        $this->HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Pet');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function PetClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Pet');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     */
    public function PetClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function ReferralClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        $this->HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Referral');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function ReferralClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Referral');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     */
    public function ReferralClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_USER_ID'])) {
            $this->HlFieldClearCache('user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function CommentsClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        $this->HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_OBJECT_ID'])) {
            /** @var DataManager $dm */
            $dm = HLBlockFactory::createTableObject('Comments');
            $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();
        }
        $this->HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     * @throws \Exception
     */
    public function CommentsClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');

        /** @var DataManager $dm */
        $dm = HLBlockFactory::createTableObject('Comments');
        $fields = $dm::query()->addFilter('=ID', $id)->addSelect('*')->exec()->fetch();

        if (isset($fields['UF_OBJECT_ID'])) {
            $this->HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws SystemException
     */
    public function CommentsClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (isset($fields['UF_OBJECT_ID'])) {
            $this->HlFieldClearCache('objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param $id
     *
     * @throws SystemException
     */
    protected function HlItemClearCache($id): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('highloadblock:item:' . $id);
        }
    }

    /**
     * @param $type
     * @param $value
     *
     * @throws SystemException
     */
    protected function HlFieldClearCache($type, $value): void
    {
        if (\defined('BX_COMP_MANAGED_CACHE')) {
            /** Очистка кеша */
            $instance = BitrixApplication::getInstance();
            $tagCache = $instance->getTaggedCache();
            $tagCache->clearByTag('highloadblock:field:' . $type . ':' . $value);
        }
    }
}
