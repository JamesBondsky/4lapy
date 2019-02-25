<?php
//TODO удалить Service?
namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Type\DateTime;
use FourPaws\App\Application as App;
use FourPaws\PersonalBundle\Exception\CouponIsNotDeactivatedException;
use FourPaws\PersonalBundle\Exception\CouponIsNotSetUsedException;
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
        global $USER;
        if (!$USER->IsAdmin())
        {
            die();
        }

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
     * @param int $id
     * @throws CouponIsNotSetUsedException
     */
    public function setUsedStatus(int $id): void
    {
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

        //TODO если достаточно марок, то сразу активировать новый купон на 10% после выполнения этой функции
        /**
         * if (!$piggyBankService->isUserHasActiveCoupon() && $piggyBankService->isEnoughMarksForFirstCoupon())
                        {   //TODO может получиться так, что юзер дважды получит купон, если успеют произойти два параллельных addFirstLevelCouponToUser() - сделать транзакцию
                            $piggyBankService->addFirstLevelCouponToUser();
                        }
         *
         */
    }
}