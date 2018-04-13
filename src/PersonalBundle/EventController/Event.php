<?php

namespace FourPaws\PersonalBundle\EventController;

use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\Mail\Event as EventMail;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\ServiceHandlerInterface;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        self::$eventManager = $eventManager;

        $prefix = 'Address';
        /** сброс кеша */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        $prefix = 'Pet';
        /** сброс кеша */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        /** обновление данных в манзане по питомцам */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix.'UpdateManzana']);
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix.'UpdateManzana']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix.'PrepareDelUpdateManzana']);
        self::initHandler($prefix . 'OnAfterDelete', [static::class, $prefix.'UpdateManzana']);


        $prefix = 'Referral';
        /** сброс кеша */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
        self::initHandler($prefix . 'OnBeforeDelete', [static::class, $prefix . 'ClearCacheDelete']);

        /** отправка письма после добавления */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'SendModerateEmail']);

        /** отправка данных в манзану после добавления */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'SendToManzana']);

        $prefix = 'Comments';
        /** сброс кеша */
        self::initHandler($prefix . 'OnAfterAdd', [static::class, $prefix . 'ClearCacheAdd']);
        self::initHandler($prefix . 'OnAfterUpdate', [static::class, $prefix . 'ClearCacheUpdate']);
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
     */
    public static function AddressClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function AddressClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Address', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function AddressClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }

        $fields = static::getHlItemFieldsById('Address', $id);

        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function PetClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('pets_user', $fields['UF_USER_ID']);
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
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Pet', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('pets_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function PetClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }

        $fields = static::getHlItemFieldsById('Pet', $id);

        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('pets_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function ReferralClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('referral_user', $fields['UF_USER_ID']);
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
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Referral', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::HlFieldClearCache('referral_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function ReferralClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }

        $fields = static::getHlItemFieldsById('Referral', $id);

        if (!empty(['UF_USER_ID'])) {
            static::HlFieldClearCache('referral_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function CommentsClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_OBJECT_ID'])) {
            static::HlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
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
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        static::HlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_OBJECT_ID'])) {
            $fields = static::getHlItemFieldsById('Comments', $id);
        }
        if (!empty($fields['UF_OBJECT_ID'])) {
            static::HlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function CommentsClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }

        $fields = static::getHlItemFieldsById('Comments', $id);

        if (!empty($fields['UF_OBJECT_ID'])) {
            static::HlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws \RuntimeException
     */
    public static function PetUpdateManzana(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        if (!empty($id) && !empty($_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id])) {
            /** для удаления */
            $fields = $_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS'];
            unset($_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS']);
        } else {
            $fields = $event->getParameter('fields');
            /** для обновления, если эти данные не пришли */
            if (!isset($fields['UF_USER_ID'])) {
                $fields = static::getHlItemFieldsById('Pet', $id);
            }
        }
        $logger = LoggerFactory::create('event_updateManzanaPets');
        try {
            $container = Application::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger->error('ошибка загрузки сервиса - ' . $e->getMessage());
            return;
        }
        try {
            $petService = $container->get('pet.service');
        } catch (ServiceCircularReferenceException|ServiceNotFoundException $e) {
            $logger->error('ошибка загрузки сервиса - ' . $e->getMessage());
            return;
        }
        try {
            $petService->updateManzanaPets((int)$fields['UF_USER_ID']);
        } catch (ApplicationCreateException|ServiceCircularReferenceException|ServiceNotFoundException $e) {
            $logger->error('ошибка загрузки сервиса - ' . $e->getMessage());
            return;
        } catch (ObjectPropertyException|InvalidIdentifierException|ConstraintDefinitionException $e) {
            $logger->error('ошибка параметров - ' . $e->getMessage());
            return;
        } catch (NotAuthorizedException $e) {
            return;
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public static function ReferralSendModerateEmail(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');

        $container = Application::getInstance()->getContainer();
        $serializer = $container->get(SerializerInterface::class);

        $entity = $serializer->fromArray($fields, Referral::class);

        EventMail::send(
            [
                'EVENT_NAME' => 'ReferralAdd',
                'LID'        => SITE_ID,
                'C_FIELDS'   => [
                    'CARD'       => $entity->getCard(),
                    'MAIN_PHONE' => tplvar('phone_main'),
                ],
            ]
        );
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ContactUpdateException
     */
    public static function ReferralSendToManzana(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');

        $container = Application::getInstance()->getContainer();
        $serializer = $container->get(SerializerInterface::class);
        $referralService = $container->get('referral.service');

        $entity = $serializer->fromArray($fields, Referral::class);

        $referralClient = $referralService->getClientReferral($entity);
        if (!empty($referralClient->contactId) && !empty($referralClient->cardNumber)) {
            $referralService->manzanaService->addReferralByBonusCardAsync($referralClient);
        }
    }

    /**
     * @param $id
     */
    protected static function HlItemClearCache($id): void
    {
        TaggedCacheHelper::clearManagedCache([
            'hlb:item:' . $id,
        ]);
    }

    /**
     * @param $type
     * @param $value
     */
    protected static function HlFieldClearCache($type, $value): void
    {
        TaggedCacheHelper::clearManagedCache([
            'hlb:field:' . $type . ':' . $value,
        ]);
    }

    /**
     * @param string $entityName
     * @param int    $id
     *
     * @return array|false
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     * @throws \Exception
     */
    protected static function getHlItemFieldsById(string $entityName, int $id)
    {
        return HLBlockFactory::createTableObject($entityName)::query()->addFilter('=ID',
            $id)->addSelect('*')->exec()->fetch();
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     */
    public function PetPrepareDelUpdateManzana(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id] = static::getHlItemFieldsById('Pet', $id);
    }
}
