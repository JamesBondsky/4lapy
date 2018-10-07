<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\HighloadBlock\DataManager;
use Bitrix\Iblock\Component\Tools;
use FourPaws\App\Application;
use FourPaws\AppBundle\Bitrix\FourPawsComponent;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use FourPaws\UserBundle\Service\UserService;


/** @noinspection AutoloadingIssuesInspection */

/** @noinspection EfferentObjectCouplingInspection
 *
 * Class FourPawsOrderCompleteComponent
 */
class FourPawsOrderInterviewComponent extends FourPawsComponent
{
    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    /** @var OrderService */
    protected $orderService;

    /** @var UserAccountService */
    protected $userAccountService;

    /** @var UserService */
    private $authUserService;


    public function __construct($component = null)
    {
        $serviceContainer          = Application::getInstance()->getContainer();
        $this->orderService        = $serviceContainer->get(OrderService::class);
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        $this->authUserService     = $serviceContainer->get(UserAuthorizationInterface::class);
        $this->userAccountService  = $serviceContainer->get(UserAccountService::class);

        parent::__construct($component);
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function prepareResult(): void
    {
        global $APPLICATION;

        $this->arResult['IS_AUTH'] = $this->authUserService->isAuthorized();

        $user         = null;
        $order        = null;
        $relatedOrder = null;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
        } catch (NotAuthorizedException $e) {
            //  Мы это пофиксим с помощью информации заказа
            //  А вообще есть кейс, что авторизован не тот, кто делал тот заказ, так что можно и отключить
        }

        try {
            /**
             * @var DataManager $hBlockInterviews
             */
            $hBlockInterviews     = Application::getInstance()->getContainer()->get('bx.hlblock.orderfeedback');
            $orderInterviewStatus = $hBlockInterviews->query()
                                                     ->setFilter(['=UF_ORDER_ID' => $this->arParams['ORDER_ID']])
                                                     ->setSelect(['UF_INTERVIEWED'])
                                                     ->exec()
                                                     ->fetch();
            if($orderInterviewStatus && $orderInterviewStatus['UF_INTERVIEWED'] === '1'){
                throw new NotFoundException('Отзыв по этому заказу уже был оставлен');
            }

            $order = $this->orderService->getOrderById(
                $this->arParams['ORDER_ID'],
                true,
                $user ? $user->getId() : null,
                $this->arParams['HASH']
            );

            if (!$user) {
                $userID = $order->getUserId();
                $user   = $this->authUserService->findOne($userID);
            }

            $accountNumber = $order->getFields()->get('ACCOUNT_NUMBER');
            if ($this->arParams['SET_TITLE'] === 'Y') {
                $APPLICATION->SetTitle("Отзыв по заказу №{$accountNumber}");
            }

            $this->arResult['USER']  = $user;
            $this->arResult['ORDER'] = $order;

        } catch (NotFoundException $e) {
            Tools::process404('', true, true, true);
        }
    }
}