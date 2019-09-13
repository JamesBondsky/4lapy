<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserSearchInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsPersonalCabinetOffersComponent extends FourPawsComponent
{
    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /** @var PersonalOffersService */
    protected $personalOffersService;

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
        $this->personalOffersService = $container->get('personal_offers.service');
    }

    /**
     * @todo кеширование
     * @throws Exception
     */
    public function prepareResult(): void
    {
        $offers = null;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            $userId = $user->getId();

            /*$navResult = new CDBResult();
            $navResult->NavNum = 'nav-more-orders';
            $navResult->NavPageSize = OrderService::ORDER_PAGE_LIMIT;
            $navResult->NavPageNomer = 1;

            $orders = $this->orderService->getUserOrders($user);
            $orderCount = $this->orderService->getUserOrdersCount($user);

            $navResult->NavRecordCount = $orderCount;
            $navResult->NavPageCount = ceil($orderCount / OrderService::ORDER_PAGE_LIMIT);*/
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return;
        }

        //$this->arResult['TOTAL_ORDER_COUNT'] = $orderCount;
        $offersCoupons = $this->personalOffersService->getActiveUserCoupons($userId, false, true);

        /** @var ArrayCollection $coupons */
        $coupons = $offersCoupons['coupons'];
        if (!$coupons->isEmpty()) {
            $couponsIdsCollection = $coupons
                ->filter(static function($coupon) {
                    return !$coupon['PERSONAL_COUPON_USER_COUPONS_UF_SHOWN'];
                })
                ->map(static function($coupon) {
                    return $coupon['PERSONAL_COUPON_USER_COUPONS_ID'];
                });
            $couponsIds = $couponsIdsCollection->getValues();
            if ($couponsIds) {
                $this->personalOffersService->setCouponShownStatus($couponsIds); // установка флага просмотренности купонов
            }
        }

        $modalCounters = CUser::GetByID($userId)->Fetch()['UF_MODALS_CNTS'];
        $newValue = explode(' ', $modalCounters);
        $newValue[0] = $newValue[0] ?: 0;
        $newValue[1] = $newValue[1] ?: 0;
        $newValue[2] = $newValue[2] ?: 0;
        $newValue[3] = 3; // прекращение показа всплывающего окна с купоном
        $newValue = implode(' ', $newValue);

        $userService = App::getInstance()->getContainer()->get(UserSearchInterface::class);
        $userService->setModalsCounters($userId, $newValue);

        $this->arResult['COUPONS'] = $offersCoupons['coupons'];
        $this->arResult['OFFERS'] = $offersCoupons['offers'];
        //$this->arResult['NAV'] = $navResult;
    }
}
