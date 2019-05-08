<?

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Exception\HLBlockNotFoundException;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\DiscountCouponTable;
use CSaleDiscount;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;
use FourPaws\PersonalBundle\Exception\CouponNotCreatedException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class PersonalOffersService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class PersonalOffersService
{
    use LoggerAwareTrait;

    public const SECOND_ORDER_OFFER_CODE = 'second_order';
    public const TIME_PASSED_AFTER_LAST_ORDER_OFFER_CODE = 'after_2_months';

    public const DISCOUNT_PREFIX = 'personal_offer';

    /** @var DataManager */
    protected $personalCouponManager;
    /** @var DataManager */
    protected $personalCouponUsersManager;

    /**
     * PersonalOffersService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('PersonalOffers'));

        $container = App::getInstance()->getContainer();
        $this->personalCouponManager = $container->get('bx.hlblock.personalcoupon');
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
    }

    /**
     * @param int $userId
     *
     * @return array
     *
     * @throws HLBlockNotFoundException
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\SystemException
     */
    public function getActiveUserCoupons(int $userId): array
    {
        if ($userId <= 0)
        {
            throw new InvalidArgumentException('can\'t get user\'s coupons. userId: ' . $userId);
        }

        $coupons = [];
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers();

        if (!$activeOffersCollection->isEmpty())
        {
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
                ->registerRuntimeField(
                    new ReferenceField(
                        'USER_COUPONS', $this->personalCouponUsersManager::getEntity()->getDataClass(),
                        Query\Join::on('this.ID', 'ref.UF_COUPON')
                            ->where('ref.UF_USER_ID', '=', $userId)
                            ->where(Query::filter()
                                ->logic('or')
                                ->where([
                                    ['ref.UF_USED', null],
                                    ['ref.UF_USED', false],
                                ])),
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()
                ->fetchAll();

            $userOffers = array_unique(array_map(function($coupon) { return $coupon['UF_OFFER']; }, $coupons));
            $offersCollection = $activeOffersCollection->filter(static function($offer) use ($userOffers) { return in_array($offer['ID'], $userOffers, true); });

            $activeOffers = $offersCollection->getValues();
            $offersOrder = [];
            foreach ($activeOffers as $key => $offer)
            {
                $offersOrder[$offer['ID']] = $key;
            }
            uasort($coupons, static function($a, $b) use($offersOrder) {
                return $offersOrder[$a['UF_OFFER']] <=> $offersOrder[$b['UF_OFFER']];
            });
        }

        $couponsCollection = new ArrayCollection($coupons);
        $result = [
            'coupons' => $couponsCollection,
            'offers' => $offersCollection,
        ];

        return $result;
    }

    /**
     * @param array $filter
     *
     * @return ArrayCollection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getActiveOffers($filter = []): ArrayCollection
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $arFilter = [
            '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
            '=ACTIVE' => 'Y',
            '=ACTIVE_DATE' => 'Y',
        ];
        if ($filter) {
            $arFilter = array_merge($arFilter, $filter);
        }

        $offers = [];
        $rsOffers = \CIBlockElement::GetList(
            [
                'DATE_ACTIVE_TO' => 'asc,nulls'
            ],
            $arFilter,
            false,
            false,
            [
                'ID',
                'PROPERTY_DISCOUNT',
                'PREVIEW_TEXT',
                'DATE_ACTIVE_TO',
            ]
        );
        while ($res = $rsOffers->GetNext())
        {
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
        if ($offerId <= 0)
        {
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
     * @param int $offerId
     * @param array $coupons
     *
     * @param bool $useOldLinkingMethod
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public function importOffers(int $offerId, array $coupons, bool $useOldLinkingMethod = false): void
    {
        if ($offerId <= 0)
        {
            throw new InvalidArgumentException('can\'t import personal offer\'s coupons. offerId: ' . $offerId);
        }

        $promoCodes = array_keys($coupons);
        $promoCodes = array_filter(array_map('trim', $promoCodes));
        foreach ($promoCodes as $promoCode)
        {
            $couponId = $this->personalCouponManager::add([
                'UF_PROMO_CODE' => $promoCode,
                'UF_OFFER' => $offerId,
                'UF_DATE_CREATED' => new DateTime(),
                'UF_DATE_CHANGED' => new DateTime(),
            ])->getId();

            $couponData = $coupons[$promoCode];
            if ($useOldLinkingMethod)
            {
                $userIds = $couponData;
            } else {
                $userIds = $couponData['users'];
            }
            foreach ($userIds as $userId)
            {
                $this->linkCouponToUser($couponId, $userId, $couponData['coupon'] ?? []);
            }
            unset($couponId);
        }
    }

    /**
     * @param string $phone
     * @param int $userId
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     */
    public function addFestivalCouponToUser(string $phone, int $userId): void
    {
        $container = App::getInstance()->getContainer();
        $festivalOffer = $this->getActiveOffers(['CODE' => 'festival']);
        if (!$festivalOffer->isEmpty()
            && ($festivalOfferId = (int)$festivalOffer->first()['ID'])
        ) {
            if ($phone) {
                /** @var DataManager $festivalUsersDataManager */
                $festivalUsersDataManager = $container->get('bx.hlblock.festivalusersdata');
                $festivalUser = $festivalUsersDataManager::query()
                    ->setFilter([
                        '=UF_PHONE' => $phone,
                        '=UF_USER' => false,
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
                    $festivalUser['UF_FESTIVAL_USER_ID'] => [$userId]
                ];
                $this->importOffers($festivalOfferId, $coupons, true);
            }
        }
    }

    /**
     * @param int $userId
     * @throws CouponNotCreatedException
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectException
     */
    public function addUniqueOfferCoupon(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('userId: ' . $userId . '. ' . __FUNCTION__);
        }

        $this->log()->info('Генерируется купон на второй заказ для пользователя ' . $userId);
        $secondOrderOffer = $this->getActiveOffers(['CODE' => 'second_order']);
        if (!$secondOrderOffer->isEmpty()
            && ($secondOrderOfferId = (int)$secondOrderOffer->first()['ID'])
        ) {
            $discountValue = (float)$secondOrderOffer->first()['PROPERTY_DISCOUNT_VALUE'];
            if ($discountValue <= 0) {
                throw new CouponNotCreatedException(__FUNCTION__ . '. Купон не удалось создать, $discountValue: ' . $discountValue);
            }

            $container = App::getInstance()->getContainer();
            /** @var PersonalOffersService $personalOffersService */
            $personalOffersService = $container->get('personal_offers.service');
            $discountId = $personalOffersService->getUniqueOfferDiscountIdByDiscountValue($discountValue);

            $promoCode = DiscountCouponTable::generateCoupon(true);

            $couponFields = [
                'DISCOUNT_ID' => $discountId,
                'COUPON' => $promoCode,
                'ACTIVE' => 'Y',
                'ACTIVE_FROM' => NULL,
                'ACTIVE_TO' => NULL,
                'TYPE' => '2', // купон на один заказ
                'USER_ID' => $userId,
                'DESCRIPTION' => '',
            ];
            $couponAddResult = DiscountCouponTable::add($couponFields);
            if (!$couponAddResult->isSuccess())
            {
                throw new CouponNotCreatedException(__FUNCTION__ . '. Купон не удалось создать. ' . implode(',', $couponAddResult->getErrorMessages()));
            }

            $coupons = [
                $promoCode => [
                    'users' => [
                        $userId
                    ],
                    'coupon' => [
                        'discountValue' => $discountValue,
                    ],
                ],
            ];
            $this->importOffers($secondOrderOfferId, $coupons);


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

    /**
     * @param int $couponId
     * @param int $userId
     * @param array $coupon
     * @throws \Bitrix\Main\ObjectException
     * @throws InvalidArgumentException
     */
    public function linkCouponToUser(int $couponId, int $userId, array $coupon = []): void
    {
        if ($couponId <= 0 || $userId <= 0) {
            throw new InvalidArgumentException(__FUNCTION__ . '. Не удалось привязать купон к пользователю. $couponId: ' . $couponId . '. $userId: ' . $userId);
        }

        $this->personalCouponUsersManager::add([
            'UF_USER_ID' => $userId,
            'UF_COUPON' => $couponId,
            'UF_DATE_CREATED' => new DateTime(),
            'UF_DATE_CHANGED' => new DateTime(),
            'UF_DISCOUNT_VALUE' => $coupon['discountValue'],
        ]);
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
        if (!$USER->IsAuthorized() || !($userId = $USER->GetID()))
        {
            return;
        }

        if ($promoCode === '')
        {
            throw new InvalidArgumentException('can\'t set Used status to promocode. Got empty promocode');
        }

        $promoCodeUserLinkId = $this->personalCouponUsersManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                'UF_USED' => false,
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

        if ($promoCodeUserLinkId > 0)
        {
            $currentDateTime = new DateTime();
            $this->personalCouponUsersManager::update($promoCodeUserLinkId, [
                'UF_USED' => true,
                'UF_DATE_CHANGED' => $currentDateTime,
                'UF_DATE_USED' => $currentDateTime,
            ]);
        }
    }

    /**
     * @param string $promoCode
     *
     * @throws CouponIsNotAvailableForUseException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkCoupon(string $promoCode)
    {
        global $USER;

        if (!$USER->IsAuthorized() || !($userId = $USER->GetID()))
        {
            return;
        }

        $isPromoCodeUsed = (bool)$this->personalCouponUsersManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                'UF_USED' => true,
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
            ->getSelectedRowsCount();

        if ($isPromoCodeUsed) {
            throw new CouponIsNotAvailableForUseException('coupon is not available for use. Promo code: ' . $promoCode . '. User id: ' . $userId);
        }
    }

    /**
     * @param string $promoCode
     *
     * @return ArrayCollection
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getOfferFieldsByPromoCode(string $promoCode): ArrayCollection
    {
        if ($promoCode === '')
        {
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
                '=UF_PROMO_CODE' => $promoCode,
            ])
            ->exec()
            ->fetch()['UF_OFFER'];
        $offer = [];
        if ($offerId)
        {
            $rsOffers = \CIBlockElement::GetList(
                [
                    'DATE_ACTIVE_TO' => 'asc,nulls'
                ],
                [
                    '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
                    '=ID' => $offerId,
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
            if ($res = $rsOffers->GetNext())
            {
                if (is_array($res))
                {
                    $offer = $res;
                }
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
                'ID'
            ]
        )->GetNext();
        if ($rsDiscount)
        {
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
}