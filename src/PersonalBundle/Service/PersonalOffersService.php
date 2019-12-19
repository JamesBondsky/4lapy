<?

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use CIBlockElement;
use CUserFieldEnum;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\External\ExpertsenderService;
use FourPaws\External\Import\Model\ImportOffer;
use FourPaws\PersonalBundle\Exception\AlreadyExistsException;
use FourPaws\PersonalBundle\Exception\BaseException;
use FourPaws\PersonalBundle\Exception\CouponNotFoundException;
use FourPaws\PersonalBundle\Exception\RuntimeException;
use FourPaws\PersonalBundle\Repository\BasketsDiscountOfferRepository;
use FourPaws\PersonalBundle\Repository\CouponPoolRepository;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserSearchInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use FourPaws\Helpers\BxCollection;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use Picqer\Barcode\BarcodeGeneratorPNG as BarcodeGeneratorPNG;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\DiscountCouponTable;
use FourPaws\App\Application as App;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\ReferenceField;
use CSaleDiscount;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\UserBundle\Repository\FestivalUsersTable;
use Adv\Bitrixtools\Exception\HLBlockNotFoundException;
use FourPaws\AppBundle\Exception\JsonResponseException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;
use FourPaws\PersonalBundle\Exception\CouponNotCreatedException;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class PersonalOffersService
 *
 * @todo    Отрефакторить класс (вынести CRUD купонов в отдельный Repository)
 * @package FourPaws\PersonalBundle\Service
 */
class PersonalOffersService
{
    use LoggerAwareTrait;

    public const SECOND_ORDER_OFFER_CODE                 = 'second_order';
    public const TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE = 'after_2_months';

    public const DISCOUNT_PREFIX = 'personal_offer';

    public const INFINITE_COUPON_DATE_FORMATTED = '01.01.3000'; // Дата, с которой Manzana устанавливает дату окончания действия бесконечных купонов

    public const NTH_BASKET_OFFER_ID = '20-20';
    public const START_DATETIME_20TH_OFFER = '19.12.2019 00:00:00'; //TODO set 25.12 after task's ready
    public const END_DATETIME_20TH_OFFER = '26.12.2019 23:59:59';

    /** @var DataManager */
    protected $personalCouponManager;
    /** @var DataManager */
    protected $personalCouponUsersManager;
    protected $serializer;

    /**
     * @var PersonalOrderService
     */
    private $personalOrderService;

    /**
     * @var OrderService $orderService
     */
    private $orderService;

    /** @var UserService */
    protected $userService;

    /** @var int */
    private $personalOfferBasketId;

    /**
     * PersonalOffersService constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService, PersonalOrderService $personalOrderService, UserSearchInterface $userService)
    {
        $this->setLogger(LoggerFactory::create('PersonalOffers'));

        $container                        = App::getInstance()->getContainer();
        $this->personalCouponManager      = $container->get('bx.hlblock.personalcoupon');
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
        $this->serializer                 = $container->get(SerializerInterface::class);
        $this->orderService               = $orderService;
        $this->personalOrderService       = $personalOrderService;
        $this->userService                = $userService;
    }

    /**
     * @param int       $userId
     * @param bool|null $isNotShown
     * @param bool|null $withUnrestrictedCoupons
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getActiveUserCoupons(int $userId, ?bool $isNotShown = false, ?bool $withUnrestrictedCoupons = false): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('can\'t get user\'s coupons. userId: ' . $userId);
        }

        [$offersCollection, $couponsCollection] = $this->getActiveCoupons($userId, $isNotShown, $withUnrestrictedCoupons);
        $result = [
            'coupons' => $couponsCollection,
            'offers'  => $offersCollection,
        ];

        return $result;
    }

    /**
     * @param int $userId
     * @return array
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getActiveUserCouponsEx(int $userId): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('can\'t get user\'s coupons. userId: ' . $userId);
        }

        [$offersCollection, $couponsCollection] = $this->getActiveCoupons($userId);

        $result = [];
        foreach ($couponsCollection as $coupon) {
            $offer = $offersCollection->get($coupon['UF_OFFER']);

            $item = [
                'id'        => $coupon['ID'],
                'promocode' => $coupon['UF_PROMO_CODE'],
            ];

            $item['discount'] = $coupon['custom_title'];

            if ($coupon['custom_date_to']) {
                $item['date_active'] = $coupon['custom_date_to'];
            }

            $item['text'] = strip_tags(html_entity_decode($offer['PREVIEW_TEXT']));
            $result[]     = $item;
        }
        return $result;
    }

    /**
     * @param array     $filter
     *
     * @param bool|null $withUnrestrictedCoupons
     * @param bool $ignoreHiddenFromAccount
     * @return ArrayCollection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws SystemException
     */
    public function getActiveOffers($filter = [], ?bool $withUnrestrictedCoupons = false, $ignoreHiddenFromAccount = true): ArrayCollection
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $arFilter = [
            '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
        ];
        if ($ignoreHiddenFromAccount) {
            $arFilter['!PROPERTY_HIDE_FROM_ACCOUNT'] = true;
        }
        if ($withUnrestrictedCoupons) {
            $arFilter[] = [
                'LOGIC'                             => 'OR',
                'PROPERTY_IS_UNRESTRICTED_ACTIVITY' => true,
                [
                    '=ACTIVE'      => 'Y',
                    '=ACTIVE_DATE' => 'Y',
                ],
            ];
        } else {
            $arFilter['=ACTIVE']      = 'Y';
            $arFilter['=ACTIVE_DATE'] = 'Y';
        }
        if ($filter) {
            $arFilter = array_merge($arFilter, $filter);
        }

        $offers   = [];
        $rsOffers = \CIBlockElement::GetList(
            [
                'DATE_ACTIVE_TO' => 'asc,nulls',
                'SORT'           => 'ASC',
            ],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_DISCOUNT',
                'PROPERTY_DISCOUNT_CURRENCY',
                'PREVIEW_TEXT',
                'DATE_ACTIVE_TO',
                'PROPERTY_ACTIVE_TO',
                'PROPERTY_COUPON_TITLE',
            ]
        );
        while ($res = $rsOffers->GetNext()) {
            $offers[$res['ID']] = $res;
        }

        return new ArrayCollection($offers);
    }

    /**
     * @param int $offerId
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isOfferCouponsImported(int $offerId): bool
    {
        if ($offerId <= 0) {
            throw new InvalidArgumentException('can\'t check personal offer\'s coupons. offerId: ' . $offerId);
        }

        return (bool)$this->personalCouponManager::query()
            ->setFilter([
                '=UF_OFFER' => $offerId,
            ])
            ->exec()
            ->getSelectedRowsCount();
    }

    /**
     * @param int         $offerId
     * @param array       $coupons
     * @param string|null $activeFrom
     * @param string|null $activeTo
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public function importOffersAsync(int $offerId, array $coupons, ?string $activeFrom = '', ?string $activeTo = ''): void
    {
        if ($offerId <= 0) {
            throw new InvalidArgumentException('can\'t import personal offer\'s coupons. offerId: ' . $offerId);
        }

        $producer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.import_offers_producer');

        foreach ($coupons as $coupon => $couponUsers) {
            foreach ($couponUsers as $couponUser) {
                $importOffer              = new ImportOffer();
                $importOffer->dateChanged = new DateTime();
                $importOffer->dateCreate  = new DateTime();
                $importOffer->offerId     = $offerId;
                $importOffer->promoCode   = $coupon;
                $importOffer->user        = $couponUser;
                $importOffer->activeFrom  = $activeFrom;
                $importOffer->activeTo    = $activeTo;

                $producer->publish($this->serializer->serialize($importOffer, 'json'));
            }
        }
    }

    /**
     * @param int   $offerId
     * @param array $coupons
     * @param bool  $useOldLinkingMethod В новом методе связывания используются два ключа - users для массива пользователей, к которым привязать промокод, и coupon для расширенной информации о
     *                                   купоне. В старом способе связывания возможно указывать только пользователей
     * @return array
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public function importOffers(int $offerId, array $coupons, bool $useOldLinkingMethod = false): array
    {
        if ($offerId <= 0) {
            throw new InvalidArgumentException('can\'t import personal offer\'s coupons. offerId: ' . $offerId);
        }

        $promoCodes = array_keys($coupons);
        $promoCodes = array_filter(array_map('trim', $promoCodes));
        $result = [];
        foreach ($promoCodes as $promoCode) {
            $couponId = $this->personalCouponManager::add([
                'UF_PROMO_CODE'   => $promoCode,
                'UF_OFFER'        => $offerId,
                'UF_DATE_CREATED' => new DateTime(),
                'UF_DATE_CHANGED' => new DateTime(),
            ])->getId();

            $couponData = $coupons[$promoCode];
            if ($useOldLinkingMethod) {
                $userIds = $couponData;
            } else {
                $userIds = $couponData['users'];
                if (!$userIds) {
                    $fUserIds = $couponData['fUsers'];
                }
            }
            $usersCouponsIds = [];
            if ($userIds) {
                foreach ($userIds as $userId) {
                    $couponLinkId = $this->linkCouponToUser($couponId, $userId, $couponData['coupon'] ?? []);
                    $usersCouponsIds[$userId] = $couponLinkId;
                }
            } else {
                foreach ($fUserIds as $fUserId) {
                    $couponLinkId = $this->linkCouponToUser($couponId, null, $couponData['coupon'] ?? [], $fUserId);
                    $usersCouponsIds[$fUserId] = $couponLinkId;
                }
            }

            $result[$promoCode] = [
                'couponId' => $couponId,
                'users' => $usersCouponsIds
            ];
            unset($couponId);
        }

        return $result;
    }

    /**
     * @param string $phone
     * @param int    $userId
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     */
    public function addFestivalCouponToUser(string $phone, int $userId): void
    {
        $container     = App::getInstance()->getContainer();
        $festivalOffer = $this->getActiveOffers(['CODE' => 'festival']);
        if (!$festivalOffer->isEmpty()
            && ($festivalOfferId = (int)$festivalOffer->first()['ID'])
        ) {
            if ($phone) {
                /** @var DataManager $festivalUsersDataManager */
                $festivalUsersDataManager = $container->get('bx.hlblock.festivalusersdata');
                $festivalUser             = $festivalUsersDataManager::query()
                    ->setFilter([
                        '=UF_PHONE' => $phone,
                        '=UF_USER'  => false,
                    ])
                    ->setSelect([
                        'ID',
                        'UF_FESTIVAL_USER_ID',
                    ])
                    ->setLimit(1)
                    ->exec()
                    ->fetch();
                if ($festivalUser) {
                    $festivalUsersDataManager::update($festivalUser['ID'], [
                        'UF_USER' => $userId,
                    ]);
                }

                $coupons = [
                    $festivalUser['UF_FESTIVAL_USER_ID'] => [$userId],
                ];
                $this->importOffers($festivalOfferId, $coupons, true);
            }
        }
    }

    /**
     * @param int         $userId
     * @param string      $personalOfferCode
     * @param string|bool $activeTime в формате аргумента DateTime::modify
     * @return bool
     * @throws CouponNotCreatedException
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function addUniqueOfferCoupon(int $userId, string $personalOfferCode, $activeTime = false): bool
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('userId: ' . $userId . '. ' . __FUNCTION__);
        }

        $this->log()->info('Генерируется купон персонального предложения ' . $personalOfferCode . ' для пользователя ' . $userId);
        $personalOffer = $this->getActiveOffers(['CODE' => $personalOfferCode]);
        if (!$personalOffer->isEmpty()
            && ($personalOfferId = (int)$personalOffer->first()['ID'])
        ) {
            $discountValue = (float)$personalOffer->first()['PROPERTY_DISCOUNT_VALUE'];
            if ($discountValue <= 0) {
                throw new CouponNotCreatedException(__FUNCTION__ . '. Купон персонального предложения ' . $personalOfferCode . ' не удалось создать, $discountValue: ' . $discountValue);
            }

            if ($activeTime) {
                $dateTimeActiveTo = (new DateTime())->add($activeTime);
            }

            // Деактивация уже имеющегося и генерирование нового купона, выдаваемого через два месяца неактивности после последнего завершенного заказа
            if ($personalOfferCode === self::TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE) {
                $existingCoupon = $this->personalCouponUsersManager::query()
                    ->setFilter([
                        'UF_USER_ID'          => $userId,
                        '>=UF_DATE_ACTIVE_TO' => new DateTime(),
                        'OFFER.UF_OFFER'      => $personalOfferId,
                        'UF_USED'             => false,
                        '=UF_DATE_USED'       => false,
                    ])
                    ->setSelect([
                        //'UF_COUPON',
                        'UF_DISCOUNT_VALUE',
                        'OFFER.UF_PROMO_CODE',
                        'ID',
                    ])
                    ->registerRuntimeField(new ReferenceField(
                        'OFFER',
                        $this->personalCouponManager::getEntity(),
                        ['=this.UF_COUPON' => 'ref.ID'],
                        ['join_type' => 'INNER']))
                    ->setOrder([
                        'ID' => 'DESC',
                    ])
                    ->setLimit(1)
                    ->exec()
                    ->fetch();

                if ($existingCoupon) {
                    $discount = DiscountCouponTable::query()
                        ->setFilter([
                            'COUPON' => $existingCoupon['PERSONAL_COUPON_USERS_OFFER_UF_PROMO_CODE'],
                        ])
                        ->setSelect([
                            'ID',
                        ])
                        ->setLimit(1)
                        ->exec()
                        ->fetch();

                    try {
                        // Старый купон деактивируется, ниже будет создан новый
                        $couponUpdateResult = $this->personalCouponUsersManager::update($existingCoupon['ID'], [
                            'UF_DATE_USED'    => new DateTime(),
                            'UF_DATE_CHANGED' => new DateTime(),
                        ]);
                        if ($couponUpdateResult->isSuccess()) {
                            $discountUpdateResult = DiscountCouponTable::update($discount['ID'], [
                                'ACTIVE' => 'N',
                            ]);

                            if (!$discountUpdateResult->isSuccess()) {
                                $updateError = implode('; ', $discountUpdateResult->getErrorMessages());
                            }
                        } else {
                            $updateError = implode('; ', $couponUpdateResult->getErrorMessages());
                        }
                    } catch (\Exception $e) {
                        $updateError = $e->getMessage();
                    }
                    if (isset($updateError)) {
                        $this->log()->critical('Не удалось деактивировать купон при обновлении. Пользователь: ' . $userId . '. $personalOfferCode: ' . $personalOfferCode . '. ' . $updateError);
                    }
                }
            }

            $container = App::getInstance()->getContainer();
            $discountId            = $this->getUniqueOfferDiscountIdByDiscountValue($discountValue);

            $promoCode = DiscountCouponTable::generateCoupon(true);

            $couponFields    = [
                'DISCOUNT_ID' => $discountId,
                'COUPON'      => $promoCode,
                'ACTIVE'      => 'Y',
                'ACTIVE_FROM' => null,
                'ACTIVE_TO'   => $dateTimeActiveTo ?? null,
                'TYPE'        => '2', // купон на один заказ
                'USER_ID'     => $userId,
                'DESCRIPTION' => '',
            ];
            $couponAddResult = DiscountCouponTable::add($couponFields);
            if (!$couponAddResult->isSuccess()) {
                throw new CouponNotCreatedException(__FUNCTION__ . '. Купон не удалось создать. ' . implode(',', $couponAddResult->getErrorMessages()));
            }

            $coupons = [
                $promoCode => [
                    'users'  => [
                        $userId,
                    ],
                    'coupon' => [
                        'discountValue'    => $discountValue,
                        'dateTimeActiveTo' => $dateTimeActiveTo ?? false,
                    ],
                ],
            ];
            $this->importOffers($personalOfferId, $coupons);

            return true;
        }
    }

    /**
     * @param int $festivalOfferId
     *
     * @return bool|int
     */
    public function getCouponIdByOfferId(int $festivalOfferId)
    {
        $coupon = $this->personalCouponManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                'UF_OFFER' => $festivalOfferId,
            ])
            ->exec()
            ->fetch();
        if ($coupon) {
            return (int)$coupon['ID'];
        }

        return false;
    }

    public function getOfferFieldsByCouponId(int $couponId): ArrayCollection
    {
        if (!$couponId) {
            throw new InvalidArgumentException('can\'t get offer by promo code. Got empty promo code');
        }
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $offerId = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
            ])
            ->setFilter([
                '=ID' => $couponId,
            ])
            ->exec()
            ->fetch()['UF_OFFER'];
        $offer   = [];
        if ($offerId) {
            $rsOffers = \CIBlockElement::GetList(
                [
                    'DATE_ACTIVE_TO' => 'asc,nulls',
                ],
                [
                    '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
                    '=ID'        => $offerId,
                ],
                false,
                ['nTopCount' => 1],
                [
                    'ID',
                    'PREVIEW_TEXT',
                    'DATE_ACTIVE_TO',
                    'PROPERTY_DISCOUNT',
                    'PROPERTY_NO_USED_STATUS',
                    'PROPERTY_ACTIVE_TO',
                    'PROPERTY_DISCOUNT_CURRENCY',
                ]
            );
            if ($res = $rsOffers->GetNext()) {
                if (is_array($res)) {
                    $offer = $res;
                }
            }
        }

        return new ArrayCollection($offer);
    }

    /**
     * @param int   $couponId
     * @param int   $userId
     * @param array $coupon
     * @param int|null $fUserId
     * @return int
     * @throws \Bitrix\Main\ObjectException
     * @throws InvalidArgumentException
     */
    public function linkCouponToUser(int $couponId, ?int $userId = null, array $coupon = [], ?int $fUserId = null): int
    {
        if ($couponId <= 0 || ($userId <= 0 && $fUserId <= 0)) {
            throw new InvalidArgumentException(__FUNCTION__ . '. Не удалось привязать купон к пользователю. $couponId: ' . $couponId . '. $userId: ' . $userId);
        }

        $addResult = $this->personalCouponUsersManager::add([
            'UF_USER_ID'          => $userId,
            'UF_FUSER_ID'         => $fUserId,
            'UF_COUPON'           => $couponId,
            'UF_DATE_CREATED'     => new DateTime(),
            'UF_DATE_CHANGED'     => new DateTime(),
            'UF_DISCOUNT_VALUE'   => $coupon['discountValue'],
            'UF_DATE_ACTIVE_FROM' => $coupon['dateTimeActiveFrom'],
            'UF_DATE_ACTIVE_TO'   => $coupon['dateTimeActiveTo'],
            'UF_MANZANA_ID'       => $coupon['manzanaId'],
        ]);

        return $addResult->getId();
    }

    /**
     * @param int $linkId
     * @throws InvalidArgumentException
     */
    public function deleteCouponUserLink(int $linkId): void
    {
        if ($linkId <= 0) {
            throw new InvalidArgumentException(__METHOD__ . '. Не удалось удалить привязку купона к пользователю. $linkId: ' . $linkId);
        }

        $this->personalCouponUsersManager::delete($linkId);
    }

    /**
     * @param string $promoCode
     *
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     */
    public function setUsedStatusByPromoCode(string $promoCode): void
    {
        global $USER;
        if (!$USER->IsAuthorized() || !($userId = $USER->GetID())) {
            return;
        }

        if ($promoCode === '') {
            throw new InvalidArgumentException('can\'t set Used status to promocode. Got empty promocode');
        }

        $promoCodeUserLinkId = $this->personalCouponUsersManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                'UF_USED'     => false,
                '=UF_USER_ID' => $userId,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponManager::getEntity()->getDataClass(),
                    Query\Join::on('this.UF_COUPON', 'ref.ID')
                        ->where('ref.UF_PROMO_CODE', '=', $promoCode),
                    ['join_type' => 'INNER']
                )
            )
            ->exec()
            ->fetch()['ID'];

        if ($promoCodeUserLinkId > 0) {
            $this->setUsedStatus($promoCodeUserLinkId);
        }
    }

    /**
     * @param string $manzanaId
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws CouponNotFoundException
     */
    public function setUsedStatusByManzanaId(string $manzanaId): void
    {
        if (($manzanaId = trim($manzanaId)) === '') {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[1], 1);
        }

        $promoCodeUserLinkId = $this->personalCouponUsersManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                '=UF_MANZANA_ID' => $manzanaId,
            ])
            ->exec()
            ->fetch()['ID'];

        if ($promoCodeUserLinkId > 0) {
            $this->setUsedStatus($promoCodeUserLinkId);
        } else {
            throw new CouponNotFoundException('Купон не найден');
        }
    }

    /**
     * @param int $promoCodeUserLinkId
     * @throws \Bitrix\Main\ObjectException
     * @throws InvalidArgumentException
     */
    private function setUsedStatus(int $promoCodeUserLinkId): void
    {
        if ($promoCodeUserLinkId <= 0) {
            throw new InvalidArgumentException(InvalidArgumentException::ERRORS[2], 2);
        }

        $currentDateTime = new DateTime();
        $this->personalCouponUsersManager::update($promoCodeUserLinkId, [
            'UF_USED'         => true,
            'UF_DATE_CHANGED' => $currentDateTime,
            'UF_DATE_USED'    => $currentDateTime,
        ]);
    }


    /**
     * @param string $promoCode
     *
     * @throws CouponIsNotAvailableForUseException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkCoupon(string $promoCode): void
    {
        global $USER;

        if (
            (!$USER->IsAuthorized() || !($userId = $USER->GetID()))
                && !$fUserId = $this->userService->getCurrentFUserId()
        ) {
            return;
        }

        $arFilter = [
            //'UF_USED' => true,
        ];
        if ($userId) {
            $arFilter['=UF_USER_ID'] = $userId;
        } elseif ($fUserId) {
            $arFilter['=UF_FUSER_ID'] = $fUserId;
        }

        $arPromoCode = $this->personalCouponUsersManager::query()
            ->setSelect([
                'ID',
                'UF_USED',
                'UF_DATE_USED',
                'USER_COUPONS.UF_OFFER',
                'UF_DATE_ACTIVE_FROM',
                'UF_DATE_ACTIVE_TO',
            ])
            ->setFilter($arFilter)
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponManager::getEntity()->getDataClass(),
                    Query\Join::on('this.UF_COUPON', 'ref.ID')
                        ->where('ref.UF_PROMO_CODE', '=', $promoCode),
                    ['join_type' => 'INNER']
                )
            )
            ->exec()
            ->fetch();

        if (!$arPromoCode && $this->isLinkToUserNeeded($promoCode)) {
            throw new CouponIsNotAvailableForUseException('Купон нельзя использовать, т.к. он еще не привязан ни к одному пользователю. Промокод: ' . $promoCode . '. User id: ' . $userId);
        }

        if ($arPromoCode) {
            $activeOffers = $this->getActiveOffers([], true, false);
            $activeOffersIds = $activeOffers->getKeys();

            if ($arPromoCode['UF_USED']
                || $arPromoCode['UF_DATE_USED']
                || ($activeOffersIds && !in_array($arPromoCode['PERSONAL_COUPON_USERS_USER_COUPONS_UF_OFFER'], $activeOffersIds, false))
                || ($arPromoCode['UF_DATE_ACTIVE_FROM'] && new DateTime() < $arPromoCode['UF_DATE_ACTIVE_FROM'])
                || ($arPromoCode['UF_DATE_ACTIVE_TO'] && new DateTime() > $arPromoCode['UF_DATE_ACTIVE_TO'])
            ) {
                throw new CouponIsNotAvailableForUseException('coupon is not available for use. Already used, deactivated or not active. Promo code: ' . $promoCode . '. User id: ' . $userId);
            }
        }
    }

    /**
     * @param string $promoCode
     *
     * @return ArrayCollection
     * @throws InvalidArgumentException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getOfferFieldsByPromoCode(string $promoCode): ArrayCollection
    {
        if ($promoCode === '') {
            throw new InvalidArgumentException('can\'t get offer by promo code. Got empty promo code');
        }
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON');

        $rsOffer = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
                'USER_COUPONS.UF_DATE_ACTIVE_TO',
            ])
            ->setFilter([
                '=UF_PROMO_CODE' => $promoCode,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponUsersManager::getEntity(),
                    $personalCouponUsersQuery,
                    ['join_type' => 'LEFT']
                )
            )
            ->exec()
            ->fetch();

        $offerId = $rsOffer['UF_OFFER'];
        $offer   = [];
        if ($offerId) {
            $offerActiveTo = $rsOffer['PERSONAL_COUPON_USER_COUPONS_UF_DATE_ACTIVE_TO'];
            $rsOffers      = \CIBlockElement::GetList(
                [
                    'DATE_ACTIVE_TO' => 'asc,nulls',
                ],
                [
                    '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
                    '=ID'        => $offerId,
                ],
                false,
                ['nTopCount' => 1],
                [
                    'ID',
                    'PREVIEW_TEXT',
                    'DATE_ACTIVE_TO',
                    'PROPERTY_DISCOUNT',
                    'PROPERTY_NO_USED_STATUS',
                ]
            );
            if ($res = $rsOffers->GetNext()) {
                if (is_array($res)) {
                    $offer = $res;
                }
            }

            if ($offerActiveTo) {
                if ($offerActiveTo < new DateTime($this::INFINITE_COUPON_DATE_FORMATTED)) { // Дата, с которой Manzana устанавливает дату окончания действия бесконечных купонов
                    $offer['custom_date_active_to'] = $offerActiveTo->format('d.m.Y');
                } else {
                    $offer['custom_date_active_to'] = '';
                }
            } else {
                $offer['custom_date_active_to'] = $offer['ACTIVE_TO'];
            }
        }

        return new ArrayCollection($offer);
    }

    /**
     * @param string $promoCode
     *
     * @return bool
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function isNoUsedStatus(string $promoCode): bool
    {
        return (bool)$this->getOfferFieldsByPromoCode($promoCode)->get('PROPERTY_NO_USED_STATUS_VALUE');
    }

    /**
     * @param float $discountValue
     *
     * @return int|bool
     */
    public function getUniqueOfferDiscountIdByDiscountValue(float $discountValue)
    {
        $rsDiscount = CSaleDiscount::GetList(
            [
                'ID' => 'DESC',
            ],
            [
                'XML_ID' => self::DISCOUNT_PREFIX . '_' . $discountValue,
            ],
            false,
            [
                'nTopCount' => 1,
            ],
            [
                'ID',
            ]
        )->GetNext();
        if ($rsDiscount) {
            return $rsDiscount['ID'];
        }

        return false;
    }

    /**
     * @return LoggerInterface
     */
    protected function log(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param array $userIds
     * @param int   $personalOfferId
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getReturningUsersCoupons(array $userIds, int $personalOfferId): array
    {
        if (!$userIds || $personalOfferId <= 0) {
            throw new InvalidArgumentException(__FUNCTION__ . '. $userIds: ' . print_r($userIds, true) . '. $personalOfferId: ' . $personalOfferId);
        }

        $coupons = $this->personalCouponUsersManager::query()
            ->setFilter([
                'UF_USER_ID'     => $userIds,
                'OFFER.UF_OFFER' => $personalOfferId,
            ])
            ->setSelect([
                'UF_USER_ID',
                new ExpressionField('LAST_DATE_CREATED', 'MAX(%s)', ['UF_DATE_CREATED']),
            ])
            ->registerRuntimeField(new ReferenceField(
                'OFFER',
                $this->personalCouponManager::getEntity(),
                ['=this.UF_COUPON' => 'ref.ID'],
                ['join_type' => 'INNER']))
            ->setGroup(['UF_USER_ID'])
            ->exec()
            ->fetchAll();

        $result = [];
        foreach ($coupons as $coupon) {
            if ($coupon['UF_USER_ID']) {
                $result[$coupon['UF_USER_ID']] = $coupon;
            }
        }

        return $result;
    }

    /**
     * @return int
     * @throws JsonResponseException
     */
    public function generateFestivalUserId(): int
    {
        $idOffset = 9999;

        $rsFestivalUserId = 0;
        try {
            $rsFestivalUserId = FestivalUsersTable::addCustomized(md5(implode(',', $arFields)));
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        }
        if ($rsFestivalUserId <= 0) {
            $logger = LoggerFactory::create('Festival');
            $logger->critical(sprintf(
                'Не удалось создать ID регистрации на фестиваль. %s method. %s',
                __METHOD__,
                $exceptionMessage ?? ''
            ));
            throw new JsonResponseException($this->ajaxMess->getSystemError());
        }

        $festivalUserId = $idOffset + $rsFestivalUserId;

        return $festivalUserId;
    }

    /**
     * @param int       $userId
     * @param bool|null $isNotShown
     * @param bool|null $withUnrestrictedCoupons
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getActiveCoupons(int $userId, ?bool $isNotShown = false, ?bool $withUnrestrictedCoupons = false): array
    {
        $coupons          = [];
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers([], $withUnrestrictedCoupons);

        if (!$activeOffersCollection->isEmpty()) {
            $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON')
                ->where([
                    ['ref.UF_USER_ID', '=', $userId],
                    ['ref.UF_DATE_USED', '=', null],
                ])
                ->where(Query::filter()
                    ->logic('or')
                    ->where([
                        ['ref.UF_DATE_ACTIVE_TO', '>', new DateTime()],
                        ['ref.UF_DATE_ACTIVE_TO', '=', null],
                    ]))
                ->where(Query::filter()
                    ->logic('or')
                    ->where([
                        ['ref.UF_DATE_ACTIVE_FROM', '<', new DateTime()],
                        ['ref.UF_DATE_ACTIVE_FROM', '=', null],
                    ]))
                ->where(Query::filter()
                    ->logic('or')
                    ->where([
                        ['ref.UF_USED', null],
                        ['ref.UF_USED', false],
                    ]));
            if ($isNotShown) {
                $personalCouponUsersQuery = $personalCouponUsersQuery->where(Query::filter()
                    ->logic('or')
                    ->where([
                        ['ref.UF_SHOWN', null],
                        ['ref.UF_SHOWN', false],
                    ]));
            }
            $coupons = $this->personalCouponManager::query()
                ->setSelect([
                    'ID',
                    'UF_OFFER',
                    'UF_PROMO_CODE',
                    'USER_COUPONS',
                ])
                ->setFilter([
                    '=UF_OFFER' => $activeOffersCollection->getKeys(),
                ])
                ->setOrder([
                    'USER_COUPONS.UF_DATE_CREATED' => 'desc',
                ])
                ->registerRuntimeField(
                    new ReferenceField(
                        'USER_COUPONS', $this->personalCouponUsersManager::getEntity()->getDataClass(),
                        $personalCouponUsersQuery,
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()
                ->fetchAll();

            $userOffers       = array_unique(array_map(function ($coupon) {
                return $coupon['UF_OFFER'];
            }, $coupons));
            $offersCollection = $activeOffersCollection->filter(static function ($offer) use ($userOffers) {
                return in_array($offer['ID'], $userOffers, true);
            });

            /*$activeOffers = $offersCollection->getValues();
            $offersOrder = [];
            foreach ($activeOffers as $key => $offer)
            {
                $offersOrder[$offer['ID']] = $key;
            }
            uasort($coupons, static function($a, $b) use($offersOrder) {
                return $offersOrder[$a['UF_OFFER']] <=> $offersOrder[$b['UF_OFFER']];
            });*/
        }

        // Формирование кастомного заголовка (размер скидки/текст)
        foreach ($coupons as $couponKey => $coupon) {
            $offer = $offersCollection->get($coupon['UF_OFFER']);

            $coupons[$couponKey]['custom_title']   = $this->getCouponTitle($coupon, $offer);
            $coupons[$couponKey]['text']           = strip_tags(html_entity_decode($offer['PREVIEW_TEXT']));
            $coupons[$couponKey]['custom_date_to'] = $this->getCouponDateToText($coupon, $offer);
        }

        $couponsCollection = new ArrayCollection($coupons);
        return [$offersCollection, $couponsCollection];
    }

    /**
     * @return int
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getDobrolapCouponCnt(): int
    {
        /** Получаем айди значения добролап */
        $userFieldEnum   = new CUserFieldEnum();
        $dobrolapEnumID  = null;
        $userFieldEnumDb = $userFieldEnum->GetList(
            [
                'ID' => 'ASC',
            ],
            [
                'USER_FIELD_NAME' => 'UF_COUPON_TYPE',
            ]
        );
        while ($enum = $userFieldEnumDb->Fetch()) {
            if ($enum['XML_ID'] == 'dobrolap') {
                $dobrolapEnumID = $enum['ID'];
                break;
            }
        }

        $coupons          = null;
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers(['?XML_ID' => 'dobrolap_']);

        $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON');

        $coupons = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
                'UF_PROMO_CODE',
                'USER_COUPONS',
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponUsersManager::getEntity(),
                    $personalCouponUsersQuery,
                    ['join_type' => 'LEFT']
                )
            )
            ->exec()
            ->fetchAll();

        return count($coupons);
    }

    /**
     * @param string $userID
     * @param string $orderID
     *
     * @return array|null
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function bindDobrolapRandomCoupon(string $userID, string $orderID, bool $fuser = false, $htmlResponse = false): ?array
    {
        try {
            $order       = $this->personalOrderService->getOrderByNumber($orderID);
            $bitrixOrder = $this->orderService->getOrderById($order->getId());
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Заказ не найден!',
            ];
        }
        /*if (!$fuser && $bitrixOrder->getUserId() != $userID) {
            return [
                'success' => false,
                'message' => 'Получение купона для текущего пользователя невозможно, так как в заказе указан другой пользователь!'
            ];
        } elseif ($fuser && $bitrixOrder->getField('BX_USER_ID') != $userID) {
            return [
                'success' => false,
                'message' => 'Получение купона для текущего пользователя невозможно, так как в заказе указан другой пользователь!'
            ];
        } elseif ($this->orderService->getOrderDeliveryCode($bitrixOrder) != DeliveryService::DOBROLAP_DELIVERY_CODE) {
            return [
                'success' => false,
                'message' => 'Неверный тип доставки в заказе!'
            ];
        } else*/
        if (!($this->orderService->getOrderPropertyByCode($bitrixOrder, 'DOBROLAP_SHELTER')->getValue() > 0)) {
            return [
                'success' => false,
                'message' => 'Данный заказ не для приюта',
            ];
        } elseif ($this->orderService->getOrderPropertyByCode($bitrixOrder, 'DOBROLAP_COUPON_ID')->getValue()) {
            $dobrolapCouponID = $this->orderService->getOrderPropertyByCode($bitrixOrder, 'DOBROLAP_COUPON_ID')->getValue();
            /** @var DataManager $personalCouponManager */
            $personalCouponManager = App::getInstance()->getContainer()->get('bx.hlblock.personalcoupon');
            $coupon                = $personalCouponManager::getById($dobrolapCouponID)->fetch();
            return [
                'success' => true,
                'message' => 'Купон уже прикреплен к данному заказу!',
                'data'    => [
                    'promocode' => $coupon['UF_PROMO_CODE'],
                ],
            ];
        }

        /** Получаем айди значения добролап */
        $userID          = $order->getUserId();
        $userFieldEnum   = new CUserFieldEnum();
        $dobrolapEnumID  = null;
        $userFieldEnumDb = $userFieldEnum->GetList(
            [
                'ID' => 'ASC',
            ],
            [
                'USER_FIELD_NAME' => 'UF_COUPON_TYPE',
            ]
        );
        while ($enum = $userFieldEnumDb->Fetch()) {
            if ($enum['XML_ID'] == 'dobrolap') {
                $dobrolapEnumID = $enum['ID'];
                break;
            }
        }

        $coupon           = null;
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers(['?XML_ID' => 'dobrolap_']);

        $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON');

        $coupon = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
                'UF_PROMO_CODE',
                'USER_COUPONS',
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null,
            ])
            ->addOrder("RAND", "ASC")
            ->registerRuntimeField(
                'RAND', ['data_type' => 'float', 'expression' => ['RAND()']]
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponUsersManager::getEntity(),
                    $personalCouponUsersQuery,
                    ['join_type' => 'LEFT']
                )
            )
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'All coupons used!',
            ];
        }

        $couponID = $coupon['ID'];

        $data = [
            'UF_COUPON'       => $couponID,
            'UF_USER_ID'      => $userID,
            'UF_DATE_CREATED' => new DateTime(),
            'UF_DATE_CHANGED' => new DateTime(),
            'UF_USED'         => false,
            'UF_SHOWN'        => false,
        ];

        $res = $this->personalCouponUsersManager::add($data);

        if (!$res->isSuccess()) {
            return [
                'success' => false,
                'message' => 'Something went wrong!',
            ];
        }

        $this->userService->sendNotifications([$userID], $couponID, null, $coupon['UF_PROMO_CODE'], new \DateTime(), null, false, 'ID');
        $this->userService->sendNotifications([$userID], $couponID, ExpertsenderService::PERSONAL_OFFER_COUPON_START_SEND_EMAIL, $coupon['UF_PROMO_CODE'], new \DateTime(), null, true, 'ID',
            $couponID);

        $freeCouponsCnt = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'USER_COUPONS',
                new ExpressionField('CNT', 'COUNT(1)'),
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'USER_COUPONS', $this->personalCouponUsersManager::getEntity(),
                    $personalCouponUsersQuery,
                    ['join_type' => 'LEFT']
                )
            )
            ->exec()->getSelectedRowsCount();

        $this->logger->notice('Количество оставшихся купонов Добролап: ' . $freeCouponsCnt);

        //Записываем айди купона в заказ
        $this->orderService->setOrderPropertyByCode($bitrixOrder, 'DOBROLAP_COUPON_ID', $couponID);
        $bitrixOrder->save();

        if ($htmlResponse) {
            $html = $this->getHtmlCoupon($coupon);
            if (!$html) {
                return [
                    'success' => false,
                    'message' => 'Something went wrong with html generator!',
                ];
            }

            return [
                'success' => true,
                'data'    => $html,
            ];
        } else {
            $offer = $this->getOfferByCoupon($coupon);
            return [
                'success' => true,
                'data'    => [
                    'dobrolap_coupon' => [
                        'personal_offer' => [
                            'id'          => $coupon['ID'],
                            'promocode'   => $coupon['UF_PROMO_CODE'],
                            'discount'    => ($offer["PROPERTY_DISCOUNT_VALUE"] ? $offer["PROPERTY_DISCOUNT_VALUE"] . "%" : $offer["PROPERTY_DISCOUNT_CURRENCY_VALUE"] . " ₽"),
                            'date_active' => 'Действует до ' . $offer['DATE_ACTIVE_TO'],
                            'text'        => $offer["PREVIEW_TEXT"],
                        ],
                        'text'           => [
                            'title'          => 'А вот и сюрприз для Вас!',
                            'description'    => 'Это ваш подарок за участие в акции. Он доступен в разделе Персональные предложения.',
                            'titleUse'       => 'Как использовать промо-код:',
                            'descriptionUse' => "1. На сайте или в мобильном приложении положите неакционные товары в корзину и введите промо-код в специальное поле в корзине.\n2. В магазине на кассе перед оплатой неакционных товаров покажите промо-код кассиру.\n3. Промо-код можно использовать 1 раз до окончания его срока действия.",
                        ],
                    ],
                ],
            ];
        }
    }

    private function getHtmlCoupon($coupon)
    {
        $html             = null;
        $barcodeGenerator = new BarcodeGeneratorPNG();
        $offer            = $this->getOfferByCoupon($coupon);

        if ($offer) {
            //FIXME Этот html практически целиком дублирует блок <div data-b-dobrolap-prizes="coupon-section"> в www/deploy/release/common/local/components/fourpaws/order.complete/templates/dobrolap/template.php:34
            //      но этот HTML отображается сразу после выбора пользователем карточки с кодом добролапа, а тот - показывается на следующих хитах на странице "Спасибо"
            $html = '<div data-b-dobrolap-prizes="coupon-section">
                        <div class="b-order__text-block">
                            <strong>А вот и сюрприз для Вас!</strong>
                            <br/><br/>
                            <div class="b-dobrolap-coupon" data-b-dobrolap-coupon data-coupon="'
                . $coupon["UF_PROMO_CODE"]
                . '">
                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--info">
                                    <div class="b-dobrolap-coupon__discount">
                                        <span class="b-dobrolap-coupon__discount-big">'
                . ($offer["PROPERTY_DISCOUNT_VALUE"] ? $offer["PROPERTY_DISCOUNT_VALUE"]
                    . "%" : $offer["PROPERTY_DISCOUNT_CURRENCY_VALUE"] . " ₽")
                . '</span>
    
                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--desktop">
                                        '
                . $offer["PREVIEW_TEXT"]
                . '
                                    </span>
    
                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--mobile">
                                        '
                . $offer["PREVIEW_TEXT"]
                . '
                                    </span>
                                    </div>
    
                                    <div class="b-dobrolap-coupon__deadline">
                                        скидка действует по&nbsp;промо-коду до&nbsp;'
                . $offer["PROPERTY_ACTIVE_TO_VALUE"]
                . '
                                    </div>
                                </div>
    
                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--promo">
                                    <div class="b-dobrolap-coupon__code">
                                        <span class="b-dobrolap-coupon__code-text">Промо-код</span>
                                        <strong>'
                . $coupon["UF_PROMO_CODE"]
                . '</strong>
    
                                        <button class="b-button b-button--outline-white b-dobrolap-coupon__code-copy" data-b-dobrolap-coupon="copy-btn">Скопировать</button>
                                    </div>
    
                                    <div class="b-dobrolap-coupon__barcode">
                                        <img src="data:image/png;base64,'
                . base64_encode($barcodeGenerator->getBarcode($coupon["UF_PROMO_CODE"], \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127))
                . '" alt="" class="b-dobrolap-coupon__barcode-image"/>
                                    </div>
    
                                    <button class="b-button b-button--outline-grey b-button--full-width b-dobrolap-coupon__email-me js-open-popup" data-b-dobrolap-coupon="email-btn" data-popup-id="send-email-personal-offers" data-id-coupon-personal-offers="'
                . $coupon["UF_PROMO_CODE"]
                . '">
                                        Отправить мне на email
                                    </button>
                                </div>
                            </div>
                        </div>
    
                        <div class="b-order__text-block">
                            Это ваш подарок за участие в акции. <br/>
                            Он доступен в разделе <a href="/personal/personal-offers/" class="b-link">Персональные предложения</a>.
                        </div>
    
                        <hr class="b-hr b-hr--order b-hr--top-line"/>
    
                        <div class="b-order__text-block">
                            <strong>Как использовать промо-код:</strong><br/><br/>
    
                            1. На сайте или в мобильном приложении положите неакционные товары в корзину и введите промо-код в специальное поле в корзине.
                            <br/>
                            2. В магазине на кассе перед оплатой неакционных товаров покажите промо-код кассиру.
                            <br/>
                            3. Промо-код можно использовать 1 раз до окончания его срока действия.
                        </div>
                    </div>';
        }
        $html = null; //@todo oops
        return $html;
    }

    /**
     * @param array $couponsIds
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function setCouponShownStatus(array $couponsIds): void
    {
        if (!$couponsIds) {
            throw new InvalidArgumentException(__METHOD__ . '. Невозможно установить статус просмотренности купонов. Пустой массив $couponsIds');
        }
        $updateResult = $this->personalCouponUsersManager::updateMulti($couponsIds, ['UF_SHOWN' => '1'], true);
        if (!$updateResult->isSuccess()) {
            throw new \Exception(__METHOD__ . '. update error(s): ' . implode('. ', $updateResult->getErrorMessages()));
        }
    }

    /**
     * @param $coupon
     *
     * @return array|null
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function getOfferByCoupon($coupon): ?array
    {
        $promocode = $coupon['UF_PROMO_CODE'];
        $offerID   = $coupon['UF_OFFER'];
        $offer     = null;
        $arFilter  = [
            '=IBLOCK_ID'   => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
            '=ACTIVE'      => 'Y',
            '=ACTIVE_DATE' => 'Y',
            'ID'           => $offerID,
        ];

        $rsOffers = \CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'PROPERTY_DISCOUNT',
                'PROPERTY_DISCOUNT_CURRENCY',
                'PREVIEW_TEXT',
                'DATE_ACTIVE_TO',
                'PROPERTY_ACTIVE_TO',
            ]
        );

        if ($res = $rsOffers->GetNext()) {
            $offer = $res;
        }

        return $offer;
    }

    /**
     * Возвращает заголовок, выводимый в описании купона.
     * Приоритеты:
     * 1. Заголовок из элемента инфоблока "Персональные предложения"
     * 2. Скидка в процентах из HL-блока с купонами
     * 3. Скидка в процентах из инфоблока "Персональные предложения"
     * 4. Скидка в рублях из инфоблока "Персональные предложения"
     * 5. Текст "Купон", если предыдущие поля не заданы
     *
     * @param array $coupon
     * @param array $offer
     * @param bool|null $useMinusSign
     * @return string
     */
    public function getCouponTitle(array $coupon, array $offer, ?bool $useMinusSign = true): string
    {
        $couponTitle = '';

        if ($offer['PROPERTY_COUPON_TITLE_VALUE']) {
            $couponTitle = $offer['PROPERTY_COUPON_TITLE_VALUE'];
        } else {
            $couponTitle = 'Купон';
        }

        if (!$offer['PROPERTY_COUPON_TITLE_VALUE']) {
            $discount = $this->getDiscountValue($coupon, $offer);
        }

        if ($discount) {
            $couponTitle = ($useMinusSign ? '-' : '') . $discount;
        }

        return $couponTitle;
    }

    /**
     * @param array $coupon
     * @param array $offer
     * @return string
     */
    public function getDiscountValue(array $coupon, array $offer): string
    {
        $discount = '';

        if (isset($coupon['PERSONAL_COUPON_USER_COUPONS_UF_DISCOUNT_VALUE'])) {
            $discount = $coupon['PERSONAL_COUPON_USER_COUPONS_UF_DISCOUNT_VALUE'] . '%';
        } elseif ($offer['PROPERTY_DISCOUNT_VALUE']) {
            $discount = $offer['PROPERTY_DISCOUNT_VALUE'] . '%';
        } elseif ($offer['PROPERTY_DISCOUNT_CURRENCY_VALUE']) {
            $discount = $offer['PROPERTY_DISCOUNT_CURRENCY_VALUE'] . ' ₽';
        }

        return $discount;
    }

    /**
     * Получение текста с датой окончания действия купона (дата купона из HL-блока приоритетнее даты перс.предложения из инфоблока)
     *
     * @param array $coupon
     * @param array $offer
     * @return mixed|string
     * @throws \Bitrix\Main\ObjectException
     */
    public function getCouponDateToText(array $coupon, array $offer)
    {
        /** @var DateTime $couponDateTo */
        if ($couponDateTo = $coupon['PERSONAL_COUPON_USER_COUPONS_UF_DATE_ACTIVE_TO']) {
            if ($couponDateTo < new DateTime($this::INFINITE_COUPON_DATE_FORMATTED)) { // Дата, с которой Manzana устанавливает дату окончания действия бесконечных купонов
                $text = $couponDateTo->format('d.m.Y');
            } else {
                $text = '';
            }
        }

        if (!isset($text)) {
            $text = $offer['PROPERTY_ACTIVE_TO_VALUE'];
        }

        if ($text) {
            $text = 'Действует до ' . $text;
        }
        return $text;
    }

    /**
     * Проверяет, является ли корзина пользователя 20-й корзиной на сайте за день
     * (проверяются только первые корзины пользователей. После сделанного заказа новая корзина снова считается первой)
     *
     * @throws RuntimeException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ArgumentException
     */

    private function checkIfNew20thBasket()
    {
        $userId = null;
        try {
            $userId = $this->userService->getCurrentUserId();
            $fUserId = $this->userService->getCurrentFUserId();
        } catch (NotAuthorizedException $e) {
            $fUserId = $this->userService->getCurrentFUserId();
        }
        if (!$userId && !$fUserId) {
            throw new RuntimeException('Не удалось проверить номер корзины пользователя, т.к. не заданы $userId и $fUserId');
        }

        $personalOfferBasket = BasketsDiscountOfferRepository::getRegisteredOfferBasket($fUserId, $userId);
        if ($personalOfferBasket && !$personalOfferBasket['promoCode']) {
            $personalOfferBasketId = $personalOfferBasket['id'];
        }
        if (!$personalOfferBasket) {
            $personalOfferBasketId = BasketsDiscountOfferRepository::addBasket($fUserId, $userId);
        }

        if (!isset($personalOfferBasketId)) {
            return false;
        }

        $this->setPersonalOfferBasketId($personalOfferBasketId);

        return $personalOfferBasketId % 1 === 0; //FIXME set % 20
    }

    /**
     * @return string|null
     * @throws RuntimeException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    private function getFreeCouponFor20thBasket(): ?string
    {
        $personalOfferId = ElementTable::query()
            ->setFilter([
                'CODE' => self::NTH_BASKET_OFFER_ID,
                'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
            ])
            ->setSelect(['ID', 'IBLOCK_ID'])
            ->exec()
            ->fetch()['ID'];

        if ($personalOfferId) {
            /** @var CouponPoolRepository $repository */
            $repository = App::getInstance()->getContainer()->get('coupon_pool.repository');

            return $repository->getFreePromoCode($personalOfferId);
        }
    }

    /**
     * @return int
     * @throws RuntimeException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function get20thBasketOfferId(): int
    {
        $offerId = ElementTable::query()
            ->where([
                ['IBLOCK_ID', IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS)],
                ['CODE', self::NTH_BASKET_OFFER_ID],
            ])
            ->setSelect(['ID'])
            ->setLimit(1)
            ->exec()
            ->fetch()['ID'];

        if ($offerId <= 0) {
            throw new RuntimeException('Не удалось найти персональное предложение "20% скидка 20-му покупателю"');
        }

        return $offerId;
    }

    /**
     * Проверяет, не запрещено ли применение указанного купона без предварительной привязки к юзеру
     * @param $promoCode
     * @return bool
     */
    public function isLinkToUserNeeded($promoCode): bool
    {
        return strpos($promoCode, 's20im') === 0;
    }

    /**
     * Привязывает промокод акции "20% 20-му покупателю" к текущему пользователю по USER_ID или FUSER_ID
     * @param string $promoCode
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ObjectException
     */
    private function link20thBasketOfferPromocode(string $promoCode): void
    {
        try {
            $userId = $this->userService->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            $fUserId = $this->userService->getCurrentFUserId();
        }
        if (!$userId && !$fUserId) {
            throw new RuntimeException('Не удалось выдать пользователю купон по акции 20-20, т.к. не заданы $userId и $fUserId');
        }

        $promoCodeArray = [
            'coupon' => [
                'dateTimeActiveFrom' => new DateTime(),
                'dateTimeActiveTo'   => new DateTime(self::END_DATETIME_20TH_OFFER),
            ],
        ];
        if ($userId) {
            $promoCodeArray['users'] = [$userId];
        } elseif ($fUserId) {
            $promoCodeArray['fUsers'] = [$fUserId];
        }

        $couponsArray = [
            $promoCode => $promoCodeArray,
        ];
        $logger = LoggerFactory::create(__CLASS__, '20-20');
        $logger->info('link20thBasketOfferPromocode: ' . print_r($couponsArray, true));
        $this->importOffers($this->get20thBasketOfferId(), $couponsArray);
    }

    /**
     * Проверяет, является ли пользователь владельцем 20-й "первой" за день корзины (во время акции "20% скидка 20-му покупателю"),
     * и, если да, выдает этому пользователю купон
     *
     * @return bool|string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws SystemException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function tryGet20thBasketOfferCoupon()
    {
        if ($this->is20thBasketOfferActive() && $this->checkIfNew20thBasket()) {
            $promoCode = $this->getFreeCouponFor20thBasket();
            if ($promoCode) { // Пользователь выиграл купон, далее делается привязка
                BasketsDiscountOfferRepository::setPromocode($this->getPersonalOfferBasketId(), $promoCode);
                $logger = LoggerFactory::create(__CLASS__, '20-20');
                $logger->info('tryGet20thBasketOfferCoupon: ' . print_r($promoCode, true));

                $this->link20thBasketOfferPromocode($promoCode);

                return $promoCode;
            }
        }

        return false;
    }

    public function is20thBasketOfferActive(): bool
    {
        return new DateTime() >= new DateTime(self::START_DATETIME_20TH_OFFER)
            && new DateTime() <= new DateTime(self::END_DATETIME_20TH_OFFER);
    }

    /**
     * @return int
     */
    private function getPersonalOfferBasketId(): int
    {
        return $this->personalOfferBasketId;
    }

    /**
     * @param int $personalOfferBasketId
     * @return PersonalOffersService
     */
    private function setPersonalOfferBasketId(int $personalOfferBasketId): PersonalOffersService
    {
        $this->personalOfferBasketId = $personalOfferBasketId;
        return $this;
    }
}
