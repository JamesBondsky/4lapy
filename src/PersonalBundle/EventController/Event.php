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
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
class Event extends BaseServiceHandler
{
    /**
     * @param EventManager $eventManager
     *
     * @return mixed|void
     */
    public static function initHandlers(EventManager $eventManager): void
    {
        parent::initHandlers($eventManager);

        $prefix = 'Address';
        /** сброс кеша */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'ClearCacheAdd']);
        static::initHandler($prefix . 'OnAfterUpdate', [self::class, ToLower($prefix) . 'ClearCacheUpdate']);
        static::initHandler($prefix . 'OnBeforeDelete', [self::class, ToLower($prefix) . 'ClearCacheDelete']);

        $prefix = 'Pet';
        /** сброс кеша */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'ClearCacheAdd']);
        static::initHandler($prefix . 'OnAfterUpdate', [self::class, ToLower($prefix) . 'ClearCacheUpdate']);
        static::initHandler($prefix . 'OnBeforeDelete', [self::class, ToLower($prefix) . 'ClearCacheDelete']);

        /** обновление данных в манзане по питомцам */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'UpdateManzana']);
        static::initHandler($prefix . 'OnAfterUpdate', [self::class, ToLower($prefix) . 'UpdateManzana']);
        static::initHandler($prefix . 'OnBeforeDelete', [self::class, ToLower($prefix) . 'PrepareDelUpdateManzana']);
        static::initHandler($prefix . 'OnAfterDelete', [self::class, ToLower($prefix) . 'UpdateManzana']);


        $prefix = 'Referral';
        /** сброс кеша */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'ClearCacheAdd']);
        static::initHandler($prefix . 'OnAfterUpdate', [self::class, ToLower($prefix) . 'ClearCacheUpdate']);
        static::initHandler($prefix . 'OnBeforeDelete', [self::class, ToLower($prefix) . 'ClearCacheDelete']);

        /** отправка письма после добавления */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'SendModerateEmail']);

        /** отправка данных в манзану после добавления */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'SendToManzana']);

        $prefix = 'Comments';
        /** сброс кеша */
        static::initHandler($prefix . 'OnAfterAdd', [self::class, ToLower($prefix) . 'ClearCacheAdd']);
        static::initHandler($prefix . 'OnAfterUpdate', [self::class, ToLower($prefix) . 'ClearCacheUpdate']);
        static::initHandler($prefix . 'OnBeforeDelete', [self::class, ToLower($prefix) . 'ClearCacheDelete']);
    }

    /**
     * @param BitrixEvent $event
     */
    public static function addressClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function addressClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;
        static::hlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Address', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function addressClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;

        $fields = static::getHlItemFieldsById('Address', $id);

        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('address_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function petClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('pets_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function petClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;
        static::hlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Pet', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('pets_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function petClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;
        $fields = static::getHlItemFieldsById('Pet', $id);

        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('pets_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function referralClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('referral_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function referralClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;
        static::hlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_USER_ID'])) {
            $fields = static::getHlItemFieldsById('Referral', $id);
        }
        if (!empty($fields['UF_USER_ID'])) {
            static::hlFieldClearCache('referral_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function referralClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;

        $fields = static::getHlItemFieldsById('Referral', $id);

        if (!empty(['UF_USER_ID'])) {
            static::hlFieldClearCache('referral_user', $fields['UF_USER_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function commentsClearCacheAdd(BitrixEvent $event): void
    {
        $fields = $event->getParameter('fields');
        if (!empty($fields['UF_OBJECT_ID'])) {
            static::hlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function commentsClearCacheUpdate(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;
        static::hlItemClearCache($id);
        $fields = $event->getParameter('fields');
        if (!isset($fields['UF_OBJECT_ID'])) {
            $fields = static::getHlItemFieldsById('Comments', $id);
        }
        if (!empty($fields['UF_OBJECT_ID'])) {
            static::hlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
        }
    }

    /**
     * @param BitrixEvent $event
     *
     * @throws \Exception
     */
    public static function commentsClearCacheDelete(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;

        $fields = static::getHlItemFieldsById('Comments', $id);

        if (!empty($fields['UF_OBJECT_ID'])) {
            static::hlFieldClearCache('comments_objectId', $fields['UF_OBJECT_ID']);
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
    public static function petUpdateManzana(BitrixEvent $event): void
    {
        // костыль для отключения обработчика
        if (isset($GLOBALS['DisablePetUpdateManzana']) && $GLOBALS['DisablePetUpdateManzana']) {
            return;
        }

        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;

        if (!empty($id) && !empty($_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id])) {
            /** для удаления */
            $fields = $_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id];
            unset($_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id]);
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
    public static function referralSendModerateEmail(BitrixEvent $event): void
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
    public static function referralSendToManzana(BitrixEvent $event): void
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
     * @param BitrixEvent $event
     *
     * @throws \Exception
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     */
    public static function petPrepareDelUpdateManzana(BitrixEvent $event): void
    {
        $id = $event->getParameter('id');
        if (\is_array($id)) {
            $id = $id['ID'];
        }
        $id = (int)$id;

        $_SESSION['EVENT_UPDATE_MANZANA_PET_FIELDS_' . $id] = static::getHlItemFieldsById('Pet', $id);
    }

    /**
     * @param $id
     */
    protected static function hlItemClearCache($id): void
    {
        TaggedCacheHelper::clearManagedCache([
            'hlb:item:' . $id,
        ]);
    }

    /**
     * @param $type
     * @param $value
     */
    protected static function hlFieldClearCache($type, $value): void
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
}
