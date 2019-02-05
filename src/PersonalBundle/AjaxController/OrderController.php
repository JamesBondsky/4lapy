<?php

namespace FourPaws\PersonalBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use FourPaws\PersonalBundle\Entity\Order;

/**
 * Class OrderSubscribeController
 *
 * @package FourPaws\PersonalBundle\AjaxController
 * @Route("/order")
 */
class OrderController extends Controller implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var AjaxMess
     */
    protected $ajaxMess;

    /**
     * OrderController constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param OrderService                 $orderService
     * @param AjaxMess                     $ajaxMess
     */
    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        OrderService $orderService,
        AjaxMess $ajaxMess
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->orderService = $orderService;
        $this->ajaxMess = $ajaxMess;
    }

    /**
     * @Route("/list/", methods={"GET"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @global $APPLICATION
     */
    public function listAction(Request $request): JsonResponse
    {
        global $APPLICATION;
        $page = $request->get('page', 1);

        try {
            $user = $this->currentUserProvider->getCurrentUser();
            $this->orderService->loadManzanaOrders($user, $page);
            $orders = $this->orderService->getUserOrders($user, $page);

            $navResult = new \CDBResult();
            $navResult->NavNum = 'nav-more-orders';
            $navResult->NavPageSize = OrderService::ORDER_PAGE_LIMIT;
            $navResult->NavPageNomer = $page;

            $orderCount = $this->orderService->getUserOrdersCount($user);

            $html = '<div class="b-account__accordion b-account__accordion--last">';

            /** @var Order $firstOrder */
            $firstOrder = $orders->first();
            $firstOrderDateUpdate = \DateTime::createFromFormat('d.m.Y H:i:s', $firstOrder->getDateUpdate()->toString());
            $currentMinusMonthDate = (new \DateTime)->modify('-1 month');
            $activeTitleShow = false;
            if ($firstOrderDateUpdate >= $currentMinusMonthDate) {
                $html .= '<div class="b-account__title" >Текущие</div ><ul class="b-account__accordion-order-list">';
                $activeTitleShow = true;
            }
            $historyTitleShow = false;
            foreach ($orders as $order) {
                $orderDateUpdate = \DateTime::createFromFormat('d.m.Y H:i:s', $order->getDateUpdate()->toString());
                if ($orderDateUpdate < $currentMinusMonthDate && !$historyTitleShow) {
                    $historyTitleShow = true;
                    if ($activeTitleShow) {
                        $html .= '</ul>';
                    }
                    $html .= '<div class="b-account__title">История</div><ul class="b-account__accordion-order-list">';
                }
                ob_start();
                $APPLICATION->IncludeComponent(
                    'fourpaws:personal.order.item',
                    '',
                    [
                        'ORDER' => $order,
                    ],
                    false,
                    [
                        'HIDE_ICONS' => 'Y',
                    ]
                );

                $html .= ob_get_clean();
            }

            $html .= '</ul></div>';

            $navResult->NavRecordCount = $orderCount;
            $navResult->NavPageCount = ceil($orderCount / OrderService::ORDER_PAGE_LIMIT);

            $html .= '<div class="b-container b-container--personal-orders"><div class="b-pagination">';
            ob_start();
            $APPLICATION->IncludeComponent(
                'bitrix:system.pagenavigation',
                'personal_order_pagination',
                [
                    'NAV_TITLE' => '',
                    'NAV_RESULT' => $navResult,
                    'SHOW_ALWAYS' => false,
                    'PAGE_PARAMETER' => 'page',
                    'AJAX_MODE' => 'N',
                ],
                null
            );
            $html .= ob_get_clean();
            $html .= '</div></div>';

            $result = JsonSuccessResponse::createWithData('', [
                'html'  => $html,
                'count' => $orders->count(),
            ]);
        } catch (NotAuthorizedException $e) {
            $result = $this->ajaxMess->getNeedAuthError();
        } catch (\Exception $e) {
            $result = $this->ajaxMess->getSystemError();
        }

        return $result;
    }
}
