<?php

//TODO del comments
//TODO move functions to Service

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

//use Bitrix\Main;
use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
//use Bitrix\Sale;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
//use FourPaws\Helpers\WordHelper;
//use FourPaws\PersonalBundle\Entity\OrderItem;
use FourPaws\PersonalBundle\Service\CouponService;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\BasketService;
//use FourPaws\StoreBundle\Service\StoreService;
//use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
//use FourPaws\UserBundle\Service\UserService;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @todo Обработчик для кнопки "обменять марки" */
/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetPiggybankComponent extends FourPawsComponent
{
	//TODO move to services some fields
    /** @var array */
    private $couponLevels;

    /** @var object CurrentUserProviderInterface */
    protected $currentUserProvider;
    /** @var OrderService */
    protected $orderService;
    /** @var \FourPaws\PersonalBundle\Service\PiggyBankService */
    protected $piggyBankService;

    /** @var OrderStorageService */
	//private $orderStorageService;



	/** @var DataManager */
	protected $couponDataManager;

    /** @var BasketService */
    protected $basketService;

    /**
     * @var StoreService
     */
    //protected $storeService;



	/**
     * AutoloadingIssuesInspection constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @throws ApplicationCreateException
     * @throws ServiceNotFoundException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = App::getInstance()->getContainer();
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->orderService = $container->get('order.service');
        $this->basketService = $container->get(BasketService::class);
        $this->couponDataManager = $container->get('bx.hlblock.coupon');
        $this->piggyBankService = $container->get('piggy_bank.service');
    }

	/**
	 * @param $params
	 * @param \FourPaws\PersonalBundle\Service\PiggyBankService $piggyBankService
	 * @return array
	 */
    public function onPrepareComponentParams($params): array
    {
		$this->couponLevels = $this->piggyBankService::COUPON_LEVELS;
        $params['COUPON_LEVELS'] = $this->couponLevels;
        $params['MARK_RATE'] = $this->piggyBankService::MARK_RATE;
        $params['MARKS_PER_RATE'] = $this->piggyBankService::MARKS_PER_RATE;
        $params['MARKS_PER_RATE_VETAPTEKA'] = $this->piggyBankService::MARKS_PER_RATE_VETAPTEKA;

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws ApplicationCreateException
     * @throws Exception
     */
    public function prepareResult(): void
    {
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return;
        }

        $this->piggyBankService->maxCouponLevelNumber = max(array_keys($this->couponLevels));
        $this->piggyBankService->couponLevelsQuantity = count($this->couponLevels);

        if ($this->arParams['UPGRADE_COUPON'] === BitrixUtils::BX_BOOL_TRUE)
        {
            $this->piggyBankService->upgradeCoupon();
        }

        $activeCoupon = $this->piggyBankService->getActiveCoupon();

        $this->piggyBankService->activeCouponLevelNumber = $activeCoupon['LEVEL'] ?: 0;


        $this->piggyBankService->marksAvailable = $this->piggyBankService->getAvailableMarksQuantity();

        $this->piggyBankService->getActiveCouponNominalPrice();

        $isUpgradeAvailable = $this->isNextLevelAvailable();
        $maximumAvailableLevel = $this->piggyBankService->getMaximumAvailableLevel();

        if (!$isUpgradeAvailable)
        {
            $marksNeeded = $this->getMarksNeeded();
        }


        /*$availableLevel = $this->getMaximumAvailableLevel();

        $marksNeeded = 0;
        if ($this->$this->piggyBankService->activeCouponLevelNumber !== $this->$this->piggyBankService->maxCouponLevelNumber)
        {
            if (!$availableLevel && $activeCoupon->isEmpty())
            {
                $marksNeeded = $this->couponLevels[1]['MARKS_TO_LEVEL_UP'] - $this->piggyBankService->marksAvailable;
                $markText = '<b>Осталось</b> ' . $marksNeeded . ' марок до скидки ' . $this->couponLevels[1]['DISCOUNT'] . '%';
            }
            elseif (!$availableLevel && !$activeCoupon->isEmpty())
            {
                $marksNeeded = $this->couponLevels[$this->$this->piggyBankService->activeCouponLevelNumber + 1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'] - $this->$this->piggyBankService->activeCouponNominalPrice - $this->piggyBankService->marksAvailable;
                $markText = '<b>Осталось</b> ' . $marksNeeded . ' марок до скидки ' . $this->couponLevels[$this->$this->piggyBankService->activeCouponLevelNumber + 1]['DISCOUNT'] . '%';
            }
            else {
                $isActiveNextType = true;
                $marksNeeded = $this->couponLevels[$i]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'] - $this->$this->piggyBankService->activeCouponNominalPrice;
                $buttonText = 'Обменять ' . $marksNeeded . ' марок на скидку ' . $this->couponLevels[$availableLevel]['DISCOUNT'] . '%';
            }
        }*/


        /*if (!$isActiveNextType)
        {
            $marksNeeded = 8; //TODO change
        }*/

		if ($this->piggyBankService->activeCouponLevelNumber < $this->piggyBankService->maxCouponLevelNumber) {
            $nextLevel = $this->piggyBankService->activeCouponLevelNumber + 1;
        } else {
            $nextLevel = false;
		}


        /*$this->arResult['TOTAL_ORDER_COUNT'] = $orderCount;
        $this->arResult['ORDERS'] = $orders ?? new ArrayCollection();
        $this->arResult['NAV'] = $navResult;*/
        $this->arResult['ACTIVE_MARKS'] = $this->piggyBankService->getActiveMarksQuantity();
        $this->arResult['ACTIVE_COUPON'] = $activeCoupon;
        if (!$activeCoupon->isEmpty())
        {
            $this->arResult['SALE_TYPE'] = $this->couponLevels[$activeCoupon['LEVEL']]['SALE_TYPE'];
            $this->arResult['CURRENT_LEVEL'] = $this->piggyBankService->activeCouponLevelNumber;
        }
        $this->arResult['IS_ACTIVE_NEXT_TYPE'] = $isUpgradeAvailable;
        $this->arResult['NEXT_SALE_TYPE'] = $this->couponLevels[$maximumAvailableLevel]['SALE_TYPE'];
        $this->arResult['MAXIMUM_AVAILABLE_LEVEL'] = $maximumAvailableLevel;
        if ($marksNeeded)
        {
            $this->arResult['MARKS_NEEDED'] = $marksNeeded;
        }
		$this->arResult['NEXT_LEVEL'] = $nextLevel;
    }

    /**
     * @return UserService
     */
    /*public function getCurrentUserService(): UserService
    {
        return $this->currentUserProvider;
    }*/



    /**
     * @return bool
     * @throws Exception
     */
    public function isNextLevelAvailable(): bool
    {
        if ($this->piggyBankService->activeCouponLevelNumber === $this->piggyBankService->maxCouponLevelNumber)
        {
            return false;
        }

        if ($this->piggyBankService->getActiveMarksQuantity() >= $this->couponLevels[$this->piggyBankService->activeCouponLevelNumber + 1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'])
        {
            return true;
        }

        return false;
    }

    /**
     * @return int|bool
     * @throws Exception
     */
    public function getMarksNeeded()
    {
        if ($this->piggyBankService->activeCouponLevelNumber === $this->piggyBankService->maxCouponLevelNumber)
        {
            return false;
        }

        return $this->couponLevels[$this->piggyBankService->activeCouponLevelNumber + 1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'] - $this->piggyBankService->getActiveMarksQuantity();
    }
}
