<?php

namespace FourPaws\PersonalBundle\EventController;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\Event as BitrixEvent;
use Bitrix\Main\EventManager;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Mail\Event as EventMail;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Internals\DiscountTable;
use CSaleDiscount;
use CUser;
use FourPaws\App\Application;
use FourPaws\App\Application as App;
use FourPaws\App\BaseServiceHandler;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockProperty;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\UserGroup;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Event
 *
 * Обработчики событий
 *
 * @package FourPaws\PersonalBundle\EventController
 */
class Event extends BaseServiceHandler
{
    /** @var array $disabledHandlers */
    protected static $disabledHandlers = [];

    /**
     * @param string $handlerName
     */
    public static function disableHandler(string $handlerName): void
    {
        static::$disabledHandlers[$handlerName] = true;
    }

    /**
     * @param string $handlerName
     */
    public static function enableHandler(string $handlerName): void
    {
        if (isset(static::$disabledHandlers[$handlerName])) {
            unset(static::$disabledHandlers[$handlerName]);
        }
    }

    /**
     * @param string $handlerName
     * @return bool
     */
    public static function isDisabledHandler(string $handlerName): bool
    {
        return isset(static::$disabledHandlers[$handlerName]);
    }

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

        /** персональные предложения */
        static::initHandler('OnBeforeIBlockElementAdd', [self::class, 'checkDateTo'], 'iblock');
        static::initHandler('OnBeforeIBlockElementUpdate', [self::class, 'checkDateTo'], 'iblock');
        static::initHandler('OnBeforeIBlockElementUpdate', [self::class, 'checkIfNewCoupons'], 'iblock');
        static::initHandler('OnAfterIBlockElementAdd', [self::class, 'importPersonalOffersCoupons'], 'iblock');
        static::initHandler('OnAfterIBlockElementUpdate', [self::class, 'importPersonalOffersCoupons'], 'iblock');
        static::initHandler('\PersonalCouponUsers::OnAfterAdd', [self::class, 'resetCouponWindowCounter']);

        static::initHandler('OnAfterIBlockElementUpdate', [self::class, 'processShareFileProducts'], 'iblock');
        static::initHandler('OnAfterIBlockElementAdd', [self::class, 'processShareFileProducts'], 'iblock');

        /** уникальные акции */
        static::initHandler('OnAfterIBlockElementAdd', [self::class, 'createDiscountFromPersonalOffer'], 'iblock');
        static::initHandler('OnAfterIBlockElementUpdate', [self::class, 'createDiscountFromPersonalOffer'], 'iblock');

        if(KioskService::isKioskMode()) {
            static::initHandler('OnEpilog', [
                self::class,
                'setKioskStore',
            ], 'main');
        }
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
        if (static::isDisabledHandler(__FUNCTION__)) {
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

    /**
     * @param $arFields
     *
     * @return bool|void
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function checkDateTo($arFields)
    {
        if ($arFields['IBLOCK_ID'] == IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS))
        {
            if ($arFields['ACTIVE_TO'] && $arFields['ACTIVE_TO'] !== (new Date($arFields['ACTIVE_TO']))->toString())
            {
                global $APPLICATION;
                $APPLICATION->ThrowException('В дате окончания активности не должны быть указаны часы и минуты');
                return false;
            }
        }
    }

    /**
     * @param $arFields
     *
     * @return bool|void
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockPropertyNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     */
    public static function checkIfNewCoupons($arFields)
    {
        if ($arFields['IBLOCK_ID'] == IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS))
        {
            $fileFieldId = IblockUtils::getPropertyId($arFields['IBLOCK_ID'], 'FILE');

            if ($fileFieldId > 0 && ($fileProperty = $arFields['PROPERTY_VALUES'][$fileFieldId]) && ($file = array_values($fileProperty)[0]['VALUE']))
            {
                $fileHandler = fopen($file['tmp_name'], 'rb'); // tmp_name есть только при добавлении/замене файла, в остальных случаях вместо хэндлера вернется false
                if ($fileHandler)
                {
                    $container = Application::getInstance()->getContainer();
                    /** @var PersonalOffersService $personalOffersService */
                    $personalOffersService = $container->get('personal_offers.service');

                    if ($personalOffersService->isOfferCouponsImported($arFields['ID']))
                    {
                        global $APPLICATION;
                        $APPLICATION->ThrowException('Купоны для этого персонального предложения уже импортированы');
                        return false;
                    }
                }
            }
        }
    }

    /**
     * @param $arFields
     *
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockPropertyNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public static function importPersonalOffersCoupons($arFields): void
    {
        set_time_limit(0);
        if ($arFields['RESULT'] && $arFields['IBLOCK_ID'] == IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS))
        {
            $fileFieldId = IblockUtils::getPropertyId($arFields['IBLOCK_ID'], 'FILE');

            if ($fileFieldId > 0 && ($fileProperty = $arFields['PROPERTY_VALUES'][$fileFieldId]) && ($file = array_values($fileProperty)[0]['VALUE']))
            {
                $fileHandler = fopen($file['tmp_name'], 'rb'); // tmp_name есть только при добавлении/замене файла, в остальных случаях вместо хэндлера вернется false
                if ($fileHandler)
                {
                    // Получение промокодов и номеров телефонов из файла
                    $phonesArray = [];
                    $coupons     = [];
                    while (!feof($fileHandler)) {
                        $couponInfo = array_map(
                            'trim',
                            explode(',', fgets($fileHandler))
                        );
                        if ($couponInfo[1]) {
                            if (!array_key_exists($couponInfo[1], $coupons)) {
                                $coupons[$couponInfo[1]] = [];
                            }
                            if (PhoneHelper::isPhone($couponInfo[0]))
                            {
                                $couponInfo[0] = PhoneHelper::formatPhone($couponInfo[0], PhoneHelper::FORMAT_SHORT);
                                if ($couponInfo[0])
                                {
                                    $phonesArray[] = $couponInfo[0];
                                    $coupons[$couponInfo[1]][$couponInfo[0]] = '';
                                }
                            }
                        }
                        unset($couponInfo);
                    }
                    fclose($fileHandler);
                    $phonesArray = array_unique(array_filter($phonesArray));

                    if ($phonesArray)
                    {
                        // Получение пользователей, которым надо выдать купоны
                        $users = UserTable::query()
                            ->setSelect([
                                'ID',
                                'PERSONAL_PHONE',
                            ])
                            ->setFilter([
                                '=PERSONAL_PHONE' => $phonesArray,
                            ])
                            ->exec()
                            ->fetchAll();
                        $userIds = [];
                        foreach ($users as $user)
                        {
                            $userIds[$user['PERSONAL_PHONE']] = $user['ID'];
                        }
                        foreach ($coupons as $promoCode => $couponUsers)
                        {
                            foreach ($couponUsers as $phone => $userId)
                            {
                                $coupons[$promoCode][$phone] = $userIds[$phone];
                            }
                        }
                    }

                    //TODO номера из $phonesArray, которых нет среди зарегистрированных пользователей ($users) - сохранить и потом перепроверять
                    // при регистрации/изменении пользователей/привязке телефонов, не появился ли такой юзер. Если появился - привязать ему купон

                    $container = Application::getInstance()->getContainer();
                    /** @var PersonalOffersService $personalOffersService */
                    $personalOffersService = $container->get('personal_offers.service');
                    $personalOffersService->importOffersAsync($arFields['ID'], $coupons, $arFields['ACTIVE_FROM']);
                }
            }
        }
    }


    public static function setKioskStore(): void
    {
        $request = Request::createFromGlobals();
        $storeCode = $request->request->get('store');
        /** @var KioskService $kioskService */
        $kioskService = Application::getInstance()->getContainer()->get('kiosk.service');

        if(!empty($storeCode) && (!$kioskService->getStore() || $storeCode != $kioskService->getStore()->getXmlId())){
            $kioskService->setStore($storeCode);
        } elseif (!$kioskService->getStore()) {
            $kioskService->setStore($kioskService->getDefaultStoreXmlId());
        }
    }

    /**
     * @param BitrixEvent $event
     */
    public static function resetCouponWindowCounter(BitrixEvent $event)
    {
        if (static::isDisabledHandler(__FUNCTION__)) {
            return;
        }

        $fields = $event->getParameter('fields');
        $userId = (int)$fields['UF_USER_ID'];
        if ($userId <= 0) {
            return;
        }

        $modalCounters = CUser::GetByID($userId)->Fetch()['UF_MODALS_CNTS'];
        $newValue = explode(' ', $modalCounters);
        $newValue[0] = $newValue[0] ?: 0;
        $newValue[1] = $newValue[1] ?: 0;
        $newValue[2] = $newValue[2] ?: 0;
        $newValue[3] = 0;
        $newValue = implode(' ', $newValue);

        $userService = Application::getInstance()->getContainer()->get(UserSearchInterface::class);
        $userService->setModalsCounters($userId, $newValue);
    }

    public static function processShareFileProducts($arFields): void
    {
        if (static::isDisabledHandler(__FUNCTION__)) {
            return;
        }

        if ($arFields['IBLOCK_ID'] == IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES)) {
            $arFilter = [
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES),
                [
                    'LOGIC' => 'or',
                    ['CODE' => IblockProperty::SHARES_FILE_IMPORT_CODE],
                    ['CODE' => IblockProperty::SHARES_PRODUCT_CODE],
                ]
            ];
            $props = \CIBlockProperty::GetList([], $arFilter);

            $propId = null;
            $productPropId = null;

            while($propsItem = $props->GetNext()) {
                if ($propsItem['CODE'] == IblockProperty::SHARES_FILE_IMPORT_CODE && is_null($propId)) {
                    $propId = $propsItem['ID'];
                }
                if ($propsItem['CODE'] == IblockProperty::SHARES_PRODUCT_CODE && is_null($productPropId)) {
                    $productPropId = $propsItem['ID'];
                }
            }

            if (isset($arFields['PROPERTY_VALUES'][$propId])) {
                $fileImport = array_shift($arFields['PROPERTY_VALUES'][$propId]);
                $fileImport = $fileImport['VALUE'];

                $products = file_get_contents($fileImport['tmp_name']);

                $productItems = explode("\n", $products);

                $productItems = array_map(function($productItem) {
                    if (!empty($productItem)) {
                        return str_replace(["\r", "\n"], '', $productItem);
                    }
                }, $productItems);

                $productItems = array_filter($productItems, function ($productItem) {
                    if (!empty($productItem)) {
                        return $productItem;
                    }
                });

                $productAddedArr = [];

                if (isset($arFields['PROPERTY_VALUES'][$productPropId])) {
                    foreach ($arFields['PROPERTY_VALUES'][$productPropId] as $productAdded) {
                        $productAddedArr[] = $productAdded['VALUE'];
                    }
                }

                $productItems = ($productItems);

                $uniqArrProducts = (array_merge($productItems, $productAddedArr));

                $uniqArrProducts = array_filter($uniqArrProducts, function ($uniqArrProduct) {
                    if (!empty($uniqArrProduct)) {
                        return $uniqArrProduct;
                    }
                });


                \CModule::IncludeModule("iblock");
                \CIBlockElement::SetPropertyValuesEx($arFields['ID'], $arFields['IBLOCK_ID'], [IblockProperty::SHARES_PRODUCT_CODE => $uniqArrProducts]);
            }
        }
    }

    /**
     * @param $arFields
     * @throws ArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Adv\Bitrixtools\Exception\IblockPropertyNotFoundException
     * @throws SystemException
     */
    public static function createDiscountFromPersonalOffer($arFields): void
    {
        if (!$arFields['RESULT']
            || !$arFields['IBLOCK_ID'] == IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS)
        ) {
            return;
        }

        $container = Application::getInstance()->getContainer();
        /** @var PersonalOffersService $personalOffersService */
        $personalOffersService = $container->get('personal_offers.service');

        if (!in_array($arFields['CODE'], [$personalOffersService::SECOND_ORDER_OFFER_CODE, $personalOffersService::TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE], true))
        {
            return;
        }

        $discountPropertyId = IblockUtils::getPropertyId($arFields['IBLOCK_ID'], 'DISCOUNT');

        if ($discountPropertyId > 0 && ($discountProperty = $arFields['PROPERTY_VALUES'][$discountPropertyId]) && ($discount = array_values($discountProperty)[0]['VALUE']) && $discount > 0)
        {
            $discountId = $personalOffersService->getUniqueOfferDiscountIdByDiscountValue($discount);

            if ($discountId <= 0)
            {
                $rsGroups = GroupTable::getList([
                    'filter' => ['=STRING_ID' => [
                        UserGroup::BASKET_RULES,
                        UserGroup::NOT_AUTH_CODE,
                    ]],
                    'select' => ['ID'],
                ]);
                $result = $rsGroups->fetchAll();
                $groupsIds = array_column($result, 'ID');

                $discountId = CSaleDiscount::Add([
                    'XML_ID' => $personalOffersService::DISCOUNT_PREFIX . '_' . $discount,
                    'LID' => 's1',
                    'NAME' => 'Персональное предложение ' . $discount . '%',
                    'ACTIVE' => BitrixUtils::BX_BOOL_TRUE,
                    'PRIORITY' => 1,
                    'SORT' => 100,
                    'LAST_LEVEL_DISCOUNT' => BitrixUtils::BX_BOOL_FALSE,
                    /*
                    // если нужно предотвратить выполнение других скидок
                    'PRIORITY' => 1000,
                    'SORT' => 1,
                    'LAST_LEVEL_DISCOUNT' => BitrixUtils::BX_BOOL_TRUE,
                    */
                    'LAST_DISCOUNT' => BitrixUtils::BX_BOOL_TRUE,
                    'USER_GROUPS' => $groupsIds,
                    'CURRENCY' => 'RUB',
                    'CONDITIONS' => [
                        'CLASS_ID' => 'CondGroup',
                        'DATA' => [
                            'All' => 'AND',
                            'True' => 'True',
                        ],
                        'CHILDREN' => [],
                    ],
                    'ACTIONS' => array(
                        'CLASS_ID' => 'CondGroup',
                        'DATA' => array(
                            'ALL' => 'AND',
                        ),
                        'CHILDREN' => array(
                            array(
                                'CLASS_ID' => 'ActSaleBsktGrp',
                                'DATA' => array(
                                    'Type' => 'Discount',
                                    'Value' => $discount,
                                    'Unit' => 'Perc',
                                    'Max' => 0,
                                    'All' => 'AND',
                                    'True' => 'True',
                                ),
                                'CHILDREN' => array(),
                            ),
                        ),
                    ),
                ]);

                if (!$discountId) {
                    global $APPLICATION;
                    $logger = LoggerFactory::create('event_createDiscountFromPersonalOffer');
                    $logger->error('Ошибка создания скидки: ' . $APPLICATION->GetException()->GetString());
                    return;
                }
                DiscountTable::setUseCoupons([$discountId], BitrixUtils::BX_BOOL_TRUE);
                //DiscountGroupTable::updateByDiscount($discountId, $groupsIds, 'Y', true);
            }
        }
    }
}
