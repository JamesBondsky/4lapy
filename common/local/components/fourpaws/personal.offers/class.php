<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\SystemException;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
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
        $offersCoupons = $this->personalOffersService->getActiveUserCoupons($userId);
        $this->arResult['COUPONS'] = $offersCoupons['coupons'];
        $this->arResult['OFFERS'] = $offersCoupons['offers'];
        //$this->arResult['NAV'] = $navResult;
    }
}
