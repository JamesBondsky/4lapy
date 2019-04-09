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
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\Enum\HlblockCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\PersonalBundle\Exception\InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;

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

    /**
     * PersonalOffersService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('PersonalOffers'));

        $container = App::getInstance()->getContainer();
        $this->personalCouponManager = $container->get('bx.hlblock.personalcoupon');
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

        $result = [];

        $activeOffersCollection = $this->getActiveOffers();

        if (!$activeOffersCollection->isEmpty())
        {
            $personalCouponUsersHlblockCode = HlblockCode::PERSONAL_COUPON_USERS;

            $personalCouponUsersTable = HighloadBlockTable::getList(['filter' => ['=NAME' => $personalCouponUsersHlblockCode]])->fetch();
            if (!$personalCouponUsersTable) {
                throw new HLBlockNotFoundException(sprintf('Highloadblock with name %s is not found.',
                    $personalCouponUsersHlblockCode
                ));
            }
            $personalCouponUsersTableClass = HighloadBlockTable::compileEntity($personalCouponUsersTable)->getDataClass();

            $coupons = $this->personalCouponManager::query()
                ->setSelect([
                    'ID',
                    'UF_OFFER',
                    'UF_PROMO_CODE',
                    'USER_COUPONS*',
                ])
                ->setFilter([
                    '=UF_OFFER' => $activeOffersCollection->getKeys(),
                ])
                ->registerRuntimeField(
                    new ReferenceField(
                        'USER_COUPONS', $personalCouponUsersTableClass,
                        Query\Join::on('this.ID', 'ref.UF_COUPON')
                            ->where('ref.UF_USER_ID', '=', $userId),
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()
                ->fetchAll();

            $activeOffers = $activeOffersCollection->getValues();
            $offersOrder = [];
            foreach ($activeOffers as $key => $offer)
            {
                $offersOrder[$offer['ID']] = $key;
            }
            uasort($coupons, static function($a, $b) use($offersOrder) {
                return $offersOrder[$a['UF_OFFER']] <=> $offersOrder[$b['UF_OFFER']];
            });

            $couponsCollection = new ArrayCollection($coupons);

            $result = [
                'coupons' => $couponsCollection,
                'offers' => $activeOffersCollection,
            ];
        }

        return $result;
    }

    /**
     * @return ArrayCollection
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getActiveOffers(): ArrayCollection
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Module iblock is not installed');
        }

        $offers = [];
        $rsOffers = \CIBlockElement::GetList(
            [
                'DATE_ACTIVE_TO' => 'asc,nulls'
            ],
            [
                '=IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::PERSONAL_OFFERS),
                '=ACTIVE' => 'Y',
                '=ACTIVE_DATE' => 'Y',
            ],
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

        $container = App::getInstance()->getContainer();
        /** @var DataManager $personalCouponManager */
        $personalCouponManager = $container->get('bx.hlblock.personalcoupon');

        return (bool)$personalCouponManager::query()
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

        $container = App::getInstance()->getContainer();
        /** @var DataManager $personalCouponManager */
        $personalCouponManager = $container->get('bx.hlblock.personalcoupon');
        /** @var DataManager $personalCouponUsersManager */
        $personalCouponUsersManager = $container->get('bx.hlblock.personalcouponusers');

        $promoCodes = array_keys($coupons);
        $promoCodes = array_filter(array_map('trim', $promoCodes));
        foreach ($promoCodes as $promoCode)
        {
            $couponId = $personalCouponManager::add([
                'UF_PROMO_CODE' => $promoCode,
                'UF_OFFER' => $offerId,
                'UF_DATE_CREATED' => new DateTime(),
                'UF_DATE_CHANGED' => new DateTime(),
            ])->getId();

            $userIds = $coupons[$promoCode];
            foreach ($userIds as $userId)
            {
                $personalCouponUsersManager::add([
                    'UF_USER_ID' => $userId,
                    'UF_COUPON' => $couponId,
                    'UF_DATE_CREATED' => new DateTime(),
                    'UF_DATE_CHANGED' => new DateTime(),
                ]);
            }
            unset($couponId);
        }
    }
}