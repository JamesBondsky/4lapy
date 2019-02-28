<?php
namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\PersonalBundle\Exception\CouponIsAlreadyUsedException;
use FourPaws\PersonalBundle\Exception\CouponIsNotDeactivatedException;
use FourPaws\PersonalBundle\Exception\CouponIsNotSetUsedException;
use FourPaws\PersonalBundle\Exception\CouponNotFoundException;
use FourPaws\PersonalBundle\Exception\CouponNotLinkedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class CouponService
 *
 * @package FourPaws\SaleBundle\Service
 */
class CouponService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var DataManager */
    protected $couponDataManager;
    /** @var CurrentUserProviderInterface */
    private $currentUserProviderInterface;

    /**
     * CouponService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('CouponService'));
        $container = App::getInstance()->getContainer();

        $this->couponDataManager = $container->get('bx.hlblock.coupon');
        $this->currentUserProviderInterface = $container->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param int $couponId
     *
     * @return void
     * @throws CouponNotLinkedException
     */
    public function linkCouponToCurrentUser(int $couponId): void
    {
        $updateResult = $this->couponDataManager::update($couponId, [
            'UF_AVAILABLE' => false,
            'UF_DATE_CHANGED' => DateTime::createFromTimestamp(time()),
            'UF_USER_ID' => $this->currentUserProviderInterface->getCurrentUserId(),
        ]);

        if (!$updateResult->isSuccess())
        {
            throw new CouponNotLinkedException(\sprintf(
                'Coupon %s couldn\'t be linked to current user: %s',
                $couponId,
                implode(', ', $updateResult->getErrorMessages())
            ));
        }
        if (!$updateResult->getAffectedRowsCount())
        {
            throw new CouponNotLinkedException(\sprintf(
                'Coupon %s doesn\'t exist',
                $couponId
            ));
        }
    }

    /**
     * @param int $id
     * @throws CouponIsNotDeactivatedException
     */
    public function deactivateCoupon(int $id): void
    {
		$updateResult = $this->couponDataManager::update($id, [
            'UF_DEACTIVATED' => true,
            'UF_DATE_CHANGED' => DateTime::createFromTimestamp(time()),
        ]);

        if (!$updateResult->isSuccess())
        {
            throw new CouponIsNotDeactivatedException(\sprintf(
                'Coupon %s couldn\'t be deactivated: %s',
                $id,
                implode(', ', $updateResult->getErrorMessages())
            ));
        }
        if (!$updateResult->getAffectedRowsCount())
        {
            throw new CouponIsNotDeactivatedException(\sprintf(
                'Coupon %s doesn\'t exist',
                $id
            ));
        }
	}

    /**
     * @param string $couponNumber
     * @return int
     * @throws CouponNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getCouponIdByNumber(string $couponNumber): int
    {
        $coupon = $this->couponDataManager::query()
            ->setSelect(['ID'])
            ->setFilter([
                'UF_COUPON' => $couponNumber,
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$coupon)
        {
            throw new CouponNotFoundException(\sprintf(
                'Coupon with %s number is not found',
                $couponNumber
            ));
        }

        return $coupon['ID'];
    }

    /**
     * @param string $couponNumber
     * @throws CouponIsAlreadyUsedException
     * @throws CouponIsNotSetUsedException
     * @throws CouponNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function setUsedStatusByNumber(string $couponNumber): void
    {
        if (strlen($couponNumber) < 4)
        {
            throw new CouponNotFoundException(\sprintf(
               'too short coupon number: %s',
                $couponNumber
            ));
        }
        $couponId = $this->getCouponIdByNumber($couponNumber);
        $this->setUsedStatus($couponId);
    }

    /**
     * @param int $id
     * @throws CouponIsAlreadyUsedException
     * @throws CouponIsNotSetUsedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function setUsedStatus(int $id): void
    {
        if (!$this->isCouponActive($id)) {
            throw new CouponIsAlreadyUsedException(\sprintf(
                'Coupon %s is already used or deactivated',
                $id
            ));
        }

        $updateResult = $this->couponDataManager::update($id, [
            'UF_USED' => true,
            'UF_DATE_CHANGED' => DateTime::createFromTimestamp(time()),
        ]);

        if (!$updateResult->isSuccess())
        {
            throw new CouponIsNotSetUsedException(\sprintf(
                'Coupon %s couldn\'t be set used: %s',
                $id,
                implode(', ', $updateResult->getErrorMessages())
            ));
        }
        if (!$updateResult->getAffectedRowsCount())
        {
            throw new CouponIsNotSetUsedException(\sprintf(
                'Coupon %s doesn\'t exist',
                $id
            ));
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function isCouponActive($id): bool
    {
        return (bool)$this->couponDataManager::getCount([
            'ID' => $id,
            'UF_DEACTIVATED' => false,
            'UF_USED'        => false,
        ]);
    }
}