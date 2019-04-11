<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockProperty;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Exception\CouponIsAlreadyMaxedException;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;
use FourPaws\PersonalBundle\Exception\CouponNoFreeItemsException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class PiggyBankService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PiggyBankService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $promoOfferDateStart;
    protected static $promoOfferDateEnd;
    protected static $couponsApplyingDateStart;
    protected static $couponsApplyingDateEnd;

    protected const MARKS = [
        'VIRTUAL' => [
            'ID' => 89617, // не использовать
            'XML_ID' => 2000341,
        ],
        'PHYSICAL' => [
            'ID' => 89728, // не использовать
            'XML_ID' => 3006077,
        ],
    ];
    public const MARK_RATE = 400;
    public const MARKS_PER_RATE = 1;
    public const MARKS_PER_RATE_VETAPTEKA = 2;
    public const PROMO_TITLE_MASK = 'Собиралка 03-04.19%';
    public const ACTION_CODE = 'kopimarki-mart2019';

    public const COUPON_LEVELS = [
        1 => [
            'LEVEL' => 1,
            'MARKS_TO_LEVEL_UP' => 7,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 7,
            'DISCOUNT' => 10,
            'SALE_TYPE' => 'small',
        ],
        2 => [
            'LEVEL' => 2,
            'MARKS_TO_LEVEL_UP' => 8,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 15,
            'DISCOUNT' => 20,
            'SALE_TYPE' => 'middle',
        ],
        3 => [
            'LEVEL' => 3,
            'MARKS_TO_LEVEL_UP' => 10,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 25,
            'DISCOUNT' => 30,
            'SALE_TYPE' => 'large',
        ],
    ];

    /** @var array */
    private $marksIds;
    /** @var int */
    private $physicalMarkId;
    /** @var int */
    private $physicalMarkXmlId;
    /** @var int */
    private $virtualMarkId;
    /** @var int */
    private $virtualMarkXmlId;
    /** @var ArrayCollection */
    public $levelsByDiscount;
    /** @var ArrayCollection */
    public $activeCoupon;
	/** @var int */
    private $userId;
    /** @var int */
    private $activeMarksQuantity;
    /** @var int */
    public $activeCouponLevelNumber;
    /** @var int */
    public $maxCouponLevelNumber;
    /** @var int */
    public $couponLevelsQuantity;
    /** @var int */
    public $marksAvailable;
    /** @var int */
    public $activeCouponNominalPrice;
    private $propertiesSingleProp;
    private $propertiesSingleProduct;

    /** @var BasketService */
    protected $basketService;
    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

	/** @var DataManager */
	protected $couponDataManager;

    /**
     * PiggyBankService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('PiggyBankService'));

        $container = App::getInstance()->getContainer();
        $this->basketService = $container->get(BasketService::class);
        $this->couponDataManager = $container->get('bx.hlblock.coupon');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

    public static function getMarkXmlIds()
    {
        return [
            self::MARKS['VIRTUAL']['XML_ID'],
            self::MARKS['PHYSICAL']['XML_ID'],
        ];
    }

	/**
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function isEnoughMarksForFirstCoupon(): bool
    {
        return $this->getAvailableMarksQuantity() >= self::COUPON_LEVELS[1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
    }

	/**
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function isUserHasActiveCoupon(): bool
    {
        $couponsCount = $this->couponDataManager::getCount([
            'UF_USER_ID'     => $this->getCurrentUserId(),
            'UF_PROMO'       => self::PROMO_TITLE_MASK,
            'UF_AVAILABLE'   => false,
            'UF_DEACTIVATED' => false,
            'UF_USED'        => false,
        ]);

        return (bool)$couponsCount;
    }

	/**
	 * @todo Что, если свободный купон успеют занять прежде, чем функция закончит выполнение?
	 * @todo Обработать ситуацию, если linkCouponToCurrentUser вернет false
	 *
	 * @return void
	 * @throws CouponNoFreeItemsException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \FourPaws\PersonalBundle\Exception\CouponNotLinkedException
	 */
    public function addFirstLevelCouponToUser(): void
    {
        /** @var CouponService $couponService */
    	$couponService = App::getInstance()->getContainer()->get('coupon.service');

        $freeCouponId = $this->getFreeCouponId(1);
	    $couponService->linkCouponToCurrentUser($freeCouponId); //TODO unlock coupon in case of exceptions (inside)
    }

	/**
	 * @todo обработать CouponNoFreeItems (закончились свободные купоны)
	 * @param int $level
	 * @return int
	 * @throws CouponNoFreeItemsException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public function getFreeCouponId(int $level): int
    {
        //TODO lock coupon until update
        $coupon = $this->couponDataManager::query()
            ->setSelect([
                'ID',
            ])
            ->setFilter([
                'UF_USER_ID'     => false,
                'UF_PROMO'       => self::PROMO_TITLE_MASK,
                'UF_AVAILABLE'   => true,
                'UF_DEACTIVATED' => false,
                'UF_USED'        => false,
                'UF_DISCOUNT'    => self::COUPON_LEVELS[$level]['DISCOUNT'],
            ])
            ->setOrder([
                'ID' => 'ASC',
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$coupon)
        {
        	throw new CouponNoFreeItemsException(sprintf(
                'No free coupons available of level %s',
                $level
	        ));
        }

        return $coupon['ID'];
    }

	/**
     * @return void
	 */
	public function upgradeCoupon(): void
    {
		try {
            $oldCoupon = $this->getActiveCoupon();
			if (!$oldCoupon->isEmpty())
            {
                $oldCouponId = $oldCoupon['ID'];
            }
            $this->activeCouponLevelNumber = $oldCoupon['LEVEL'] ?: 0;
            $this->getActiveCouponNominalPrice();

			/*if ($oldCoupon->isEmpty())
			{
				throw new NoActiveUserCouponException('User has no active coupon');
			}*/
			if (!$oldCoupon->isEmpty() && $oldCoupon['LEVEL'] >= max(array_keys(self::COUPON_LEVELS)))
			{
                throw new CouponIsAlreadyMaxedException('User already has max level coupon');
			}

	        /** @var CouponService $couponService */
	        $couponService = App::getInstance()->getContainer()->get('coupon.service');

            $this->marksAvailable = $this->getAvailableMarksQuantity();

            $maxAvailableLevel = $this->getMaximumAvailableLevel();
            if (!$maxAvailableLevel)
            {
                throw new \Exception('No available coupon level upgrade'); //FIXME new Exception type
            }

	        $freeCouponId = $this->getFreeCouponId($maxAvailableLevel);
	        $couponService->linkCouponToCurrentUser($freeCouponId); //TODO unlock coupon in case of exceptions (inside)
			//TODO транзакция (если не получен новый купон, то не гасить старый)

            if (isset($oldCouponId))
            {
                $couponService->deactivateCoupon($oldCouponId); //FIXME what to do if can't be deactivated?
            }

            $currentCoupon = $this->getActiveCoupon(true);
            $this->activeCouponLevelNumber = $currentCoupon['LEVEL'] ?: 0;
        } catch (\Exception $e) {
            $this->log()->critical(\sprintf(
                'Not possible to upgrade coupon: %s: %s',
                \get_class($e),
                $e->getMessage()
            ));
        }
	}

	/**
	 * @fixme Отрабатывает два раза, должен один
	 *
	 * @return int
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function getAvailableMarksQuantity(): int
    {
		$availableMarksQuantity = $this->getAllMarksQuantity() - $this->getUsedMarksQuantity();

		if ($availableMarksQuantity < 0)
		{
			//TODO Exception / Пользователем получено на ' . abs($availableMarksQuantity) . ' марок меньше, чем потрачено
			// TODO обработчик аномальной ситуации, если общее количество полученных марок меньше, чем количество потраченных марок
		}

        return $availableMarksQuantity;
    }

    /**
     * @return int
     * @todo исключить дубли, создаваемые при импорте из Manzana
     */
    public function getAllMarksQuantity(): int
    {
        return $this->basketService->getMarksQuantityFromUserBaskets();
    }

    /**
	 * @return int
	 *
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function getUsedMarksQuantity(): int
    {
        $allUserCoupons = $this->couponDataManager::query()
	        ->setSelect(['UF_DISCOUNT'])
	        ->setFilter([
                'UF_PROMO'       => self::PROMO_TITLE_MASK,
                'UF_AVAILABLE'   => false,
                'UF_DEACTIVATED' => false,
                'UF_USER_ID'     => $this->getCurrentUserId(),
	        ])
	        ->exec()
	        ->fetchAll();

        $usedMarksQuantity = array_reduce(array_column($allUserCoupons, 'UF_DISCOUNT'), function($carry, $discountValue) {
        	$carry += self::COUPON_LEVELS[$this->getLevelByDiscountValue($discountValue)]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
            return $carry;
        }, 0);

        return $usedMarksQuantity;
    }

    /**
	 * @param int $discountValue
	 * @return int
	 */
	public function getLevelByDiscountValue(int $discountValue): int
    {
    	if (!$this->levelsByDiscount)
	    {
            $levelsByDiscount = [];
            foreach (self::COUPON_LEVELS as $level => $levelInfo)
            {
                $levelsByDiscount[$levelInfo['DISCOUNT']] = $level;
		    }

            $this->levelsByDiscount = new ArrayCollection($levelsByDiscount);
	    }

    	return $this->levelsByDiscount[$discountValue];
    }

    /**
     * @todo вынести основную логику в CouponService, добавив фильтр по UF_PROMO
     *
     * @param bool $refresh
     * @return ArrayCollection
     *
     * @throws \Exception
     */
    public function getActiveCoupon(bool $refresh = false): ArrayCollection //TODO возвращать Coupon (сделать Entity)
    {
    	if (!$refresh && $this->activeCoupon)
	    {
	    	return $this->activeCoupon;
	    }

    	try {
	        $coupon = $this->couponDataManager::query()
	            ->setSelect([
	            		'ID',
	            		'UF_COUPON',
	            		'UF_DISCOUNT',
	                ])
	            ->setFilter([
	                'UF_USER_ID'     => $this->getCurrentUserId(),
	                'UF_PROMO'       => self::PROMO_TITLE_MASK,
	                'UF_AVAILABLE'   => false,
	                'UF_DEACTIVATED' => false,
	                'UF_USED'        => false,
	            ])
		        ->setOrder([
	                'UF_DISCOUNT' => 'DESC',
	                'UF_COUPON' => 'ASC',
		        ])
		        ->setLimit(1) // одновременно несколько купонов по акции "Копилка-собиралка" быть не может, но на всякий случай берется купон с наибольшей скидкой
	            ->exec()
	            ->fetch();

	        if ($coupon)
	        {
	            $coupon = [
                    'ID' => $coupon['ID'],
                    'LEVEL' => $this->getLevelByDiscountValue($coupon['UF_DISCOUNT']),
	                'COUPON_NUMBER' => $coupon['UF_COUPON'],
	                'DISCOUNT' => $coupon['UF_DISCOUNT'],
	            ];
	        }
	        else
	        {
                $coupon = [];
	        }

	    } catch (\Exception $e) {
    		//TODO log errors
            $coupon = [];
	    }

        $this->activeCoupon = new ArrayCollection($coupon);

        if (empty($coupon) && $this->getActiveMarksQuantity(true) >= self::COUPON_LEVELS[1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'])
        {
        	//TODO обработать: аномальная ситуация, 15 марок уже есть, а первый купон еще не получен (т.е. не сработал обработчик, автоматически дающий купон 1-го уровня)
        }

        return $this->activeCoupon;
    }

    /**
     * @param string $couponNumber
     * @throws CouponIsNotAvailableForUseException
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function checkPiggyBankCoupon(string $couponNumber): void
    {
        global $USER;

        if ($this->isCouponNumberFormatOk($couponNumber) && $this->isPiggyBankCoupon($couponNumber))
        {
            //if(($this->isPiggyBankCouponsApplyingDateExpired() && !$USER->IsAdmin()) || !$this->isCouponAvailableToCurrentUser($couponNumber)) {
            if($this->isPiggyBankCouponsApplyingDateExpired() || !$this->isCouponAvailableToCurrentUser($couponNumber)) {
                throw new CouponIsNotAvailableForUseException('coupon is not available for use. Promo code: ' . $couponNumber);
            }
        }
    }

    /**
     * @param string $couponNumber
     * @return bool
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function isPiggyBankCoupon(string $couponNumber): bool
    {
        if (!$this->isCouponNumberFormatOk($couponNumber)) return false;

        return (bool)$this->couponDataManager::getCount([
            'UF_COUPON'      => $couponNumber,
            'UF_PROMO'       => self::PROMO_TITLE_MASK,
        ]);
    }

    /**
     * @param string $couponNumber
     * @return bool
     * @throws SystemException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function isCouponAvailableToCurrentUser(string $couponNumber): bool
    {
        if (!$this->isCouponNumberFormatOk($couponNumber)) return false;

        return (bool)$this->couponDataManager::getCount([
            'UF_COUPON'      => $couponNumber,
            'UF_PROMO'       => self::PROMO_TITLE_MASK,
            'UF_USER_ID'     => $this->getCurrentUserId(),
            'UF_AVAILABLE'   => false,
            'UF_DEACTIVATED' => false,
            'UF_USED'        => false,
        ]);
    }

    /**
     * @param string $couponNumber
     * @return bool
     */
    public function isCouponNumberFormatOk(string $couponNumber): bool
    {
        return strlen($couponNumber) >= 4;
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ObjectException
     */
    public function isPiggyBankDateExpired(): bool
    {
        $currentDateTime = new DateTime();
        $promoOfferDateRange = $this->getPromoOfferDateRange();

        return (
            $promoOfferDateRange->get('start') > $currentDateTime ||
            $promoOfferDateRange->get('end') < $currentDateTime
        );
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\ObjectException
     */
    public function isPiggyBankCouponsApplyingDateExpired(): bool
    {
        $currentDateTime = new DateTime();
        $couponsApplyingDateRange = $this->getCouponsApplyingDateRange();

        return (
            $couponsApplyingDateRange->get('start') > $currentDateTime ||
            $couponsApplyingDateRange->get('end') < $currentDateTime
        );
    }

    /**
     * @return ArrayCollection
     * @throws \Bitrix\Main\ObjectException
     */
    public function getPromoOfferDateRange(): ArrayCollection
    {
        if (!self::$promoOfferDateStart || !self::$promoOfferDateEnd)
        {
            self::$promoOfferDateStart = new DateTime('01.03.2019 00:00:00');
            self::$promoOfferDateEnd   = new DateTime('30.04.2019 23:59:59');
        }

        return new ArrayCollection([
            'start' => self::$promoOfferDateStart,
            'end'   => self::$promoOfferDateEnd,
        ]);
    }

    /**
     * @return ArrayCollection
     * @throws \Bitrix\Main\ObjectException
     */
    public function getCouponsApplyingDateRange(): ArrayCollection
    {
        if (!self::$couponsApplyingDateStart || !self::$couponsApplyingDateEnd)
        {
            self::$couponsApplyingDateStart = new DateTime('01.03.2019 00:00:00');
            self::$couponsApplyingDateEnd   = new DateTime('20.05.2019 23:59:59');
        }

        return new ArrayCollection([
            'start' => self::$couponsApplyingDateStart,
            'end'   => self::$couponsApplyingDateEnd,
        ]);
    }

    /**
	 * @param bool $withoutCoupons
	 * @return int
     * @throws \Exception
	 */
    public function getActiveMarksQuantity(bool $withoutCoupons = false): int
    {
        if ($this->activeMarksQuantity)
        {
            return $this->activeMarksQuantity;
        }

        $activeMarks = 0;

        if (!$this->getActiveCoupon()->isEmpty())
        {
            $activeMarks += self::COUPON_LEVELS[$this->getActiveCoupon()['LEVEL']]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
        }

        $activeMarks += $this->getAvailableMarksQuantity();

        $this->activeMarksQuantity = $activeMarks;

        return $this->activeMarksQuantity;
    }

    /**
     * @return array
     */
    public function getMarksIds(): array
    {
        if ($this->marksIds)
        {
            return $this->marksIds;
        }

        $marksIds = array_map(function($mark) { return $this->getElementIdByXmlId($mark['XML_ID']); }, self::MARKS);

        $this->marksIds = $marksIds;
        return $this->marksIds;
    }

    /**
     * @param int $xmlId
     * @return int
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     */
    public function getElementIdByXmlId(int $xmlId)
    {
        return ElementTable::getList([
            'select' => ['ID', 'XML_ID'],
            'filter' => ['XML_ID' => $xmlId],
            'limit' => 1,
        ])->fetch()['ID'];
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getPhysicalMarkId(): int
    {
        if ($this->physicalMarkId)
        {
            return $this->physicalMarkId;
        }

        $this->physicalMarkId = $this->getElementIdByXmlId($this->getPhysicalMarkXmlId());
        return $this->physicalMarkId;
    }

    /**
     * @return int
     */
    public function getPhysicalMarkXmlId(): int
    {
        if ($this->physicalMarkXmlId)
        {
            return $this->physicalMarkXmlId;
        }

        $physicalMarkXmlId = self::MARKS['PHYSICAL']['XML_ID'];
        $this->physicalMarkXmlId = $physicalMarkXmlId;

        return $this->physicalMarkXmlId;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getVirtualMarkId(): int
    {
        if ($this->virtualMarkId)
        {
            return $this->virtualMarkId;
        }

        $this->virtualMarkId = $this->getElementIdByXmlId($this->getVirtualMarkXmlId());
        return $this->virtualMarkId;
    }

    /**
     * @return int
     */
    public function getVirtualMarkXmlId(): int
    {
        if ($this->virtualMarkXmlId)
        {
            return $this->virtualMarkXmlId;
        }

        $virtualMarkXmlId = self::MARKS['VIRTUAL']['XML_ID'];
        $this->virtualMarkXmlId = $virtualMarkXmlId;

        return $this->virtualMarkXmlId;
    }

	/**
	 * @return int
	 */
	private function getCurrentUserId(): int
    {
    	if ($this->userId)
	    {
	    	return $this->userId;
	    }

        $this->userId = $this->currentUserProvider->getCurrentUserId();
    	return $this->userId;
    }

    /**
     * @return int|bool
     */
    public function getMaximumAvailableLevel()
    {
        if ($this->activeCouponLevelNumber !== $this->maxCouponLevelNumber)
        {
            for ($i = $this->couponLevelsQuantity; $i > $this->activeCouponLevelNumber; --$i)
            {
                if ($this->marksAvailable >= self::COUPON_LEVELS[$i]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'] - $this->activeCouponNominalPrice)
                {
                    $availableLevel = $i;
                    break;
                }
            }
        }

        return $availableLevel ?? false;
    }

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function getActiveCouponNominalPrice(): int
    {
        $this->activeCouponNominalPrice = 0;
        $activeCoupon = $this->getActiveCoupon();
        if (!$activeCoupon->isEmpty())
        {
            for ($i = 1; $i <= $this->activeCouponLevelNumber; ++$i)
            {
                $this->activeCouponNominalPrice += self::COUPON_LEVELS[$i]['MARKS_TO_LEVEL_UP'];
            }
        }

        return $this->activeCouponNominalPrice;
    }

	/**
	 * @return LoggerInterface
	 */
	protected function log(): LoggerInterface
	{
	    return $this->logger;
	}

    /**
     * @param array $ids
     * @return array
     * @throws \Exception
     */
    public function fetchItems(array $ids): array
    {

	    $catalogIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
	    $offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);

        $cml2LinkProperty = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $offersIblockId,
                'CODE' => IblockProperty::OFFERS_LINK,
            ],
            'select' => ['ID'],
        ])->fetch();

        $licenseProperty = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $catalogIblockId,
                'CODE' => IblockProperty::LICENSE,
            ],
            'select' => ['ID'],
        ])->fetch();

        if (!$this->propertiesSingleProp)
        {
            $this->propertiesSingleProp = Base::compileEntity(
                'PROPERTIES_SINGLE_PROP', [
                    'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                    'PROPERTY_'.$cml2LinkProperty['ID'] => ['data_type' => 'integer'],
                ], ['table_name' => 'b_iblock_element_prop_s'.$offersIblockId]
            );
        }

        if (!$this->propertiesSingleProduct)
        {
            $this->propertiesSingleProduct = Base::compileEntity(
                'PROPERTIES_SINGLE_PRODUCT', [
                    'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                    'PROPERTY_'.$licenseProperty['ID'] => ['data_type' => 'integer'],
                ], ['table_name' => 'b_iblock_element_prop_s'.$catalogIblockId]
            );
        }

        $rsItems = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'IBLOCK_ID' => $offersIblockId,
                'ACTIVE' => 'Y',
                'ID' => $ids,
            ],
            'select' => [
                'ID',
                'PROPERTIES_SINGLE_PROP.PROPERTY_'.$cml2LinkProperty['ID'],
                'PROPERTIES_SINGLE_PRODUCT.PROPERTY_'.$licenseProperty['ID'],
                //'PROPERTY_LICENSE_VALUE' => 'PROPERTIES_SINGLE.PROPERTY_'.$licenseProperty['ID'],
            ],
            'runtime' => [
                'PROPERTIES_SINGLE_PROP' => [
                    'data_type' => $this->propertiesSingleProp->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner'
                ],
                'PROPERTIES_SINGLE_PRODUCT' => [
                    'data_type' => $this->propertiesSingleProduct->getDataClass(),
                    'reference' => ['this.PROPERTIES_SINGLE_PROP.PROPERTY_'.$cml2LinkProperty['ID'] => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner'
                ],
            ],
        ]);

        $arItems = [];
        while ($arItem = $rsItems->fetch()) {
            $arItems[$arItem['ID']] = [
                'ID' => $arItem['ID'],
                'IS_VETAPTEKA' => $arItem['IBLOCK_ELEMENT_PROPERTIES_SINGLE_PRODUCT_PROPERTY_'.$licenseProperty['ID']] == 1 ? true : false,
            ];
        }

        return $arItems;
    }
}