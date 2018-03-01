<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\BasketViewService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FastOrderController
 *
 * @package FourPaws\SaleBundle\AjaxController
 * @Route("/fast_order")
 */
class FastOrderController extends Controller
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var OrderStorageService
     */
    private $orderStorageService;

    /**
     * @var UserAuthorizationInterface
     */
    private $userAuthProvider;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;
    /** @var AjaxMess */
    private $ajaxMess;
    /** @var BasketService */
    private $basketService;
    /** @var BasketViewService */
    private $basketViewService;

    /**
     * OrderController constructor.
     *
     * @param OrderService                 $orderService
     * @param OrderStorageService          $orderStorageService
     * @param UserAuthorizationInterface   $userAuthProvider
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param AjaxMess                     $ajaxMess
     * @param BasketService                $basketService
     * @param BasketViewService            $basketViewService
     */
    public function __construct(
        OrderService $orderService,
        OrderStorageService $orderStorageService,
        UserAuthorizationInterface $userAuthProvider,
        CurrentUserProviderInterface $currentUserProvider,
        AjaxMess $ajaxMess,
        BasketService $basketService,
        BasketViewService $basketViewService
    ) {
        $this->orderService = $orderService;
        $this->orderStorageService = $orderStorageService;
        $this->userAuthProvider = $userAuthProvider;
        $this->currentUserProvider = $currentUserProvider;
        $this->ajaxMess = $ajaxMess;
        $this->basketService = $basketService;
        $this->basketViewService = $basketViewService;
    }

    /**
     * @Route("/load/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     */
    public function loadAction(Request $request): JsonResponse
    {
        $basketData = [];
        $requestType = $request->get('type');
        if ($requestType === 'card') {
            $basketController = new BasketController($this->basketService, $this->basketViewService);
            $response = $basketController->addAction($request);
            if ($response->isOk()) {
                if ($response instanceof JsonErrorResponse) {
                    return $response;
                }
                $basketData = json_decode($response->getContent());
            } else {
                return $this->ajaxMess->getSystemError();
            }
        }
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'fourpaws:fast.order',
            '',
            [
                'TYPE'         => 'innerForm',
                'REQUEST_TYPE' => $requestType,
            ],
            null,
            ['HIDE_ICONS' => 'Y']
        );
        $html = ob_get_clean();
        $data = ['html' => $html];
        if (!empty($basketData['miniBasket'])) {
            $data['miniBasket'] = $basketData['miniBasket'];
        }
        return JsonSuccessResponse::createWithData('подгружено', $data);
    }

    /**
     * @Route("/create/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     */
    public function createAction(Request $request): JsonResponse
    {
        $orderStorage = $this->orderStorageService->getStorage();
        $phone = $request->get('phone', '');
        $name = $request->get('name', '');

        $orderStorage->setPhone($phone);
        $orderStorage->setName($name);
        $orderStorage->setFuserId($this->currentUserProvider->getCurrentFUserId());

        if ($this->userAuthProvider->isAuthorized()) {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
                $orderStorage->setEmail($user->getEmail());
            } catch (NotAuthorizedException $e) {
                /** никогда не сработает */
            }
        }

        try {
            $order = $this->orderService->createOrder($orderStorage);
        } catch (ArgumentOutOfRangeException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (ArgumentTypeException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (ArgumentException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (NotImplementedException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (NotSupportedException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (ObjectNotFoundException $e) {
            return $this->ajaxMess->getSystemError();
        } catch (OrderCreateException $e) {
            return $this->ajaxMess->getOrderCreateError($e->getMessage());
        } catch (\Exception $e) {
            return $this->ajaxMess->getSystemError();
        }
        if ($order instanceof Order && $order->getId() > 0) {

            if($request->get('type') === 'card') {
                ob_start();
                require_once App::getDocumentRoot()
                    . '/local/components/fourpaws/fast.order/templates/.default/success.php';
                $html = ob_get_clean();

                return JsonSuccessResponse::createWithData('Быстрый заказ успешно создан', ['html' => $html]);
            }
            else{
                return JsonSuccessResponse::create('Быстрый заказ успешно создан', 200, [], ['redirect'=>'']);
            }
        }

        return $this->ajaxMess->getOrderCreateError();
    }
}
