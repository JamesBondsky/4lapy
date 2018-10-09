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
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     *
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

            $html = '';

            foreach ($orders as $order) {
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
