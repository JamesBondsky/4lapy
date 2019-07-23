<?

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use CUserFieldEnum;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Service\OrderService as PersonalOrderService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Entity\User;
use Picqer\Barcode\BarcodeGeneratorPNG as BarcodeGeneratorPNG;
use Psr\Log\LoggerAwareTrait;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Entity\ReferenceField;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\UserBundle\Repository\FestivalUsersTable;
use Adv\Bitrixtools\Exception\HLBlockNotFoundException;
use FourPaws\AppBundle\Exception\JsonResponseException;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;

/**
 * Class PersonalOffersService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class PersonalOffersService
{
    use LoggerAwareTrait;

    /** @var DataManager */
    protected $personalCouponManager;
    /** @var DataManager */
    protected $personalCouponUsersManager;

    /**
     * @var PersonalOrderService
     */
    private $personalOrderService;

    /**
     * @var OrderService $orderService
     */
    private $orderService;

    /**
     * PersonalOffersService constructor.
     *
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService, PersonalOrderService $personalOrderService)
    {
        $this->setLogger(LoggerFactory::create('PersonalOffers'));

        $container = App::getInstance()->getContainer();
        $this->personalCouponManager = $container->get('bx.hlblock.personalcoupon');
        $this->personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');
        $this->orderService = $orderService;
        $this->personalOrderService = $personalOrderService;
    }

    /**
     * @param int $userId
     * @param bool|null $isNotShown
     * @return array
     * @throws InvalidArgumentException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getActiveUserCoupons(int $userId, ?bool $isNotShown = false): array
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException('can\'t get user\'s coupons. userId: ' . $userId);
        }

        list($offersCollection, $couponsCollection) = $this->getActiveCoupons($userId, $isNotShown);
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

        list($offersCollection, $couponsCollection) = $this->getActiveCoupons($userId);

        $result = [];
        foreach ($couponsCollection as $coupon) {
            $offer = $offersCollection->get($coupon['UF_OFFER']);

            $item = [
                'id'        => $coupon['ID'],
                'promocode' => $coupon['UF_PROMO_CODE']
            ];

            if ($offer['PROPERTY_DISCOUNT_VALUE']) {
                $item['discount'] = $offer['PROPERTY_DISCOUNT_VALUE'] . '%';
            } elseif ($offer['PROPERTY_DISCOUNT_CURRENCY_VALUE']) {
                $item['discount'] = $offer['PROPERTY_DISCOUNT_CURRENCY_VALUE'] . ' ₽';
            }

            if ($offer['PROPERTY_ACTIVE_TO_VALUE']) {
                $item['date_active'] = 'Действует до ' . $offer['PROPERTY_ACTIVE_TO_VALUE'];
            }

            $item['text'] = strip_tags(html_entity_decode($offer['PREVIEW_TEXT']));
            $result[] = $item;
        }
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
                'PROPERTY_DISCOUNT_CURRENCY',
                'PREVIEW_TEXT',
                'DATE_ACTIVE_TO',
                'PROPERTY_ACTIVE_TO'
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
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ObjectException
     */
    public function importOffers(int $offerId, array $coupons): void
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

            $userIds = $coupons[$promoCode];
            foreach ($userIds as $userId)
            {
                $this->personalCouponUsersManager::add([
                    'UF_USER_ID' => $userId,
                    'UF_COUPON' => $couponId,
                    'UF_DATE_CREATED' => new DateTime(),
                    'UF_DATE_CHANGED' => new DateTime(),
                ]);
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
        /** @var PersonalOffersService $personalOffersService */
        $personalOffersService = $container->get('personal_offers.service');
        $festivalOffer = $personalOffersService->getActiveOffers(['CODE' => 'festival']);
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
                /** @var PersonalOffersService $personalOffersService */
                $personalOffersService = $container->get('personal_offers.service');
                $personalOffersService->importOffers($festivalOfferId, $coupons);
            }
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
     * @throws \Bitrix\Main\ObjectException
     * @throws InvalidArgumentException
     */
    public function linkCouponToUser(int $couponId, int $userId): void
    {
        if ($couponId <= 0 || $userId <= 0) {
            throw new InvalidArgumentException('Не удалось привязать купон к пользователю. $couponId: ' . $couponId . '. $userId: ' . $userId);
        }

        $this->personalCouponUsersManager::add([
            'UF_USER_ID' => $userId,
            'UF_COUPON' => $couponId,
            'UF_DATE_CREATED' => new DateTime(),
            'UF_DATE_CHANGED' => new DateTime(),
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
     * @param int $userId
     * @param bool|null $isNotShown
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function getActiveCoupons(int $userId, ?bool $isNotShown = false): array
    {
        $coupons = [];
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers();

        if (!$activeOffersCollection->isEmpty()) {
            $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON')
                ->where('ref.UF_USER_ID', '=', $userId)
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

            $userOffers = array_unique(array_map(function ($coupon) {
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
        $userFieldEnum = new CUserFieldEnum();
        $dobrolapEnumID = null;
        $userFieldEnumDb = $userFieldEnum->GetList(
            [
                'ID' => 'ASC'
            ],
            [
                'USER_FIELD_NAME' => 'UF_COUPON_TYPE'
            ]
        );
        while ($enum = $userFieldEnumDb->Fetch()) {
            if ($enum['XML_ID'] == 'dobrolap') {
                $dobrolapEnumID = $enum['ID'];
                break;
            }
        }

        $coupons = null;
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers(['?XML_ID' => 'dobrolap_']);

        $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON');

        $coupons = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
                'UF_PROMO_CODE',
                'USER_COUPONS'
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null
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
     * @param string   $userID
     * @param string $orderID
     *
     * @return array|null
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function bindDobrolapRandomCoupon(string $userID, string $orderID, bool $fuser = false): ?array
    {
        try {
            $order = $this->personalOrderService->getOrderByNumber($orderID);
            $bitrixOrder = $this->orderService->getOrderById($order->getId());
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Order not found!'
            ];
        }
        if (!$fuser && $bitrixOrder->getUserId() != $userID) {
            return [
                'success' => false,
                'message' => 'different current user and user in order!'
            ];
        } elseif ($fuser && $bitrixOrder->getField('BX_USER_ID') != $userID) {
            return [
                'success' => false,
                'message' => 'different current user and user in order!'
            ];
        } elseif ($this->orderService->getOrderDeliveryCode($bitrixOrder) != DeliveryService::DOBROLAP_DELIVERY_CODE) {
            return [
                'success' => false,
                'message' => 'delivery in order is not valid!'
            ];
        } elseif ($this->orderService->getOrderPropertyByCode($bitrixOrder, 'DOBROLAP_COUPON_ID')->getValue()) {
            return [
                'success' => false,
                'message' => 'another coupon already attached to the order!'
            ];
        }

        /** Получаем айди значения добролап */
        $userID = $order->getUserId();
        $userFieldEnum = new CUserFieldEnum();
        $dobrolapEnumID = null;
        $userFieldEnumDb = $userFieldEnum->GetList(
            [
                'ID' => 'ASC'
            ],
            [
                'USER_FIELD_NAME' => 'UF_COUPON_TYPE'
            ]
        );
        while ($enum = $userFieldEnumDb->Fetch()) {
            if ($enum['XML_ID'] == 'dobrolap') {
                $dobrolapEnumID = $enum['ID'];
                break;
            }
        }

        $coupon = null;
        $offersCollection = new ArrayCollection();

        $activeOffersCollection = $this->getActiveOffers(['?XML_ID' => 'dobrolap_']);

        $personalCouponUsersQuery = Query\Join::on('this.ID', 'ref.UF_COUPON');

        $coupon = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'UF_OFFER',
                'UF_PROMO_CODE',
                'USER_COUPONS'
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null
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
                'message' => 'All coupons used!'
            ];
        }

        $couponID = $coupon['ID'];

        $data = [
            'UF_COUPON'       => $couponID,
            'UF_USER_ID'      => $userID,
            'UF_DATE_CREATED' => new DateTime(),
            'UF_DATE_CHANGED' => new DateTime(),
            'UF_USED'         => false,
            'UF_SHOWN'        => false
        ];

        $res = $this->personalCouponUsersManager::add($data);

        if(!$res->isSuccess()){
            return [
                'success' => false,
                'message' => 'Something went wrong!'
            ];
        }

        $freeCouponsCnt = $this->personalCouponManager::query()
            ->setSelect([
                'ID',
                'USER_COUPONS',
                new ExpressionField('CNT', 'COUNT(1)')
            ])
            ->setFilter([
                '=UF_OFFER'                       => $activeOffersCollection->getKeys(),
                '=UF_COUPON_TYPE'                 => $dobrolapEnumID,
                'PERSONAL_COUPON_USER_COUPONS_ID' => null
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

        $html = $this->getHtmlCoupon($coupon);
        if(!$html){
            return [
                'success' => false,
                'message' => 'Something went wrong with html generator!'
            ];
        }

        return [
            'success' => true,
            'html' => $html
        ];
    }

    private function getHtmlCoupon($coupon)
    {
        $html = null;
        $barcodeGenerator = new BarcodeGeneratorPNG();
        $offer = $this->getOfferByCoupon($coupon);

        if($offer) {
            $html = '<div data-b-dobrolap-prizes="coupon-section">
                        <div class="b-order__text-block">
                            <strong>А вот и сюрприз для Вас!</strong>
                            <br/><br/>
                            <div class="b-dobrolap-coupon" data-b-dobrolap-coupon data-coupon="' . $coupon["UF_PROMO_CODE"] . '">
                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--info">
                                    <div class="b-dobrolap-coupon__discount">
                                        <span class="b-dobrolap-coupon__discount-big">' . ($offer["PROPERTY_DISCOUNT_VALUE"] ? $offer["PROPERTY_DISCOUNT_VALUE"] . "%" : $offer["PROPERTY_DISCOUNT_CURRENCY_VALUE"] . " ₽") . '</span>
    
                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--desktop">
                                        ' . $offer["PREVIEW_TEXT"] . '
                                    </span>
    
                                        <span class="b-dobrolap-coupon__discount-text b-dobrolap-coupon__discount-text--mobile">
                                        ' . $offer["PREVIEW_TEXT"] . '
                                    </span>
                                    </div>
    
                                    <div class="b-dobrolap-coupon__deadline">
                                        скидка действует по&nbsp;промо-коду до&nbsp;' . $offer["PROPERTY_ACTIVE_TO_VALUE"] . '
                                    </div>
                                </div>
    
                                <div class="b-dobrolap-coupon__item b-dobrolap-coupon__item--promo">
                                    <div class="b-dobrolap-coupon__code">
                                        <span class="b-dobrolap-coupon__code-text">Промо-код</span>
                                        <strong>' . $coupon["UF_PROMO_CODE"] . '</strong>
    
                                        <button class="b-button b-button--outline-white b-dobrolap-coupon__code-copy" data-b-dobrolap-coupon="copy-btn">Скопировать</button>
                                    </div>
    
                                    <div class="b-dobrolap-coupon__barcode">
                                        <img src="data:image/png;base64,' . base64_encode($barcodeGenerator->getBarcode($coupon["UF_PROMO_CODE"], \Picqer\Barcode\BarcodeGenerator::TYPE_CODE_128, 2.132310384278889, 127)) . '" alt="" class="b-dobrolap-coupon__barcode-image"/>
                                    </div>
    
                                    <button class="b-button b-button--outline-grey b-button--full-width b-dobrolap-coupon__email-me" data-b-dobrolap-coupon="email-btn">
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
    
                            1. На сайте или в мобильном приложении положите акционный товар в корзину и введите промо-код в специальное поле в корзине.
                            <br/>
                            2. В магазине на кассе перед оплатой акционного товара покажите промо-код кассиру.
                            <br/>
                            3. Промо-код можно использовать 1 раз до окончанчания его срока действия.
                        </div>
                    </div>';
        }

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
     * @param $offerID
     *
     * @return array|null
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     */
    public function getOfferByCoupon($coupon): ?array
    {
        $promocode = $coupon['UF_PROMO_CODE'];
        $offerID = $coupon['UF_OFFER'];
        $offer = null;
        $arFilter = [
            '=IBLOCK_ID'   => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
            '=ACTIVE'      => 'Y',
            '=ACTIVE_DATE' => 'Y',
            'ID'           => $offerID
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
                'PROPERTY_ACTIVE_TO'
            ]
        );

        if ($res = $rsOffers->GetNext()) {
            $offer = $res;
        }

        return $offer;
    }
}