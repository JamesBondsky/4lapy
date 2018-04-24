<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\AjaxController;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\App\Response\JsonErrorResponse;
use FourPaws\App\Response\JsonResponse;
use FourPaws\App\Response\JsonSuccessResponse;
use FourPaws\AppBundle\Service\AjaxMess;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\SaleBundle\Discount\Manzana;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\BaseExceptionInterface;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\BasketViewService;
use FourPaws\SaleBundle\Service\OrderService;
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
     * @param UserAuthorizationInterface   $userAuthProvider
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param AjaxMess                     $ajaxMess
     * @param BasketService                $basketService
     * @param BasketViewService            $basketViewService
     */
    public function __construct(
        OrderService $orderService,
        UserAuthorizationInterface $userAuthProvider,
        CurrentUserProviderInterface $currentUserProvider,
        AjaxMess $ajaxMess,
        BasketService $basketService,
        BasketViewService $basketViewService
    ) {
        $this->orderService = $orderService;
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
     */
    public function loadAction(Request $request): JsonResponse
    {
        $basketData = [];
        $addData = [];
        $requestType = $request->get('type', 'basket');
        if ($requestType === 'card') {
            /** add to basket
             * @see \FourPaws\SaleBundle\AjaxController\BasketController
             */
            $offerId = (int)$request->get('offerId', 0);
            if ($offerId === 0) {
                $offerId = (int)$request->get('offerid', 0);
            }
            $quantity = (int)$request->get('quantity', 1);

            try {
                $this->basketService->addOfferToBasket($offerId, $quantity);
                $addData = [
                    'miniBasket' => $this->basketViewService->getMiniBasketHtml(true),
                ];

            } catch (BaseExceptionInterface $e) {
                return $this->ajaxMess->getSystemError();
            } catch (LoaderException|ObjectNotFoundException|\RuntimeException $e) {
                $logger = LoggerFactory::create('system');
                $logger->critical('Ошибка загрузки сервисов - ' . $e->getMessage());
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
        if (!empty($addData['miniBasket'])) {
            $data['miniBasket'] = $addData['miniBasket'];
        }
        return JsonSuccessResponse::createWithData('подгружено', $data);
    }

    /**
     * @Route("/create/", methods={"GET"})
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request): JsonResponse
    {
        $orderStorage = new OrderStorage();
        try {
            $phone = PhoneHelper::normalizePhone($request->get('phone', ''));
        } catch (WrongPhoneNumberException $e) {
            return $this->ajaxMess->getWrongPhoneNumberException();
        }
        $name = $request->get('name', '');

        $orderStorage->setPhone($phone)
            ->setName($name)
            ->setFuserId($this->currentUserProvider->getCurrentFUserId())
            /** оплата наличными при доставке ставим всегда */
            ->setPaymentId(PaySystemActionTable::query()->setSelect(['ID'])->setFilter(['CODE' => 'cash'])->setCacheTtl(360000)->exec()->fetch()['ID']);

        if ($this->userAuthProvider->isAuthorized()) {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
                $orderStorage->setEmail($user->getEmail());
                $orderStorage->setUserId($user->getId());
            } catch (NotAuthorizedException $e) {
                /** никогда не сработает */
            } catch (InvalidIdentifierException|ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error('Ошибка параметров - ' . $e->getMessage());
            }
        }

        try {
            $order = $this->orderService->initOrder($orderStorage, null, null, true);
            $this->orderService->saveOrder($order, $orderStorage, null, true);
            if ($order instanceof Order && $order->getId() > 0) {
                if ($request->get('type', 'basket') === 'card') {
                    ob_start();
                    require_once App::getDocumentRoot()
                        . '/local/components/fourpaws/fast.order/templates/.default/success.php';
                    $html = ob_get_clean();

                    return JsonSuccessResponse::createWithData('Быстрый заказ успешно создан', [
                        'html'       => $html,
                        'miniBasket' => $this->basketViewService->getMiniBasketHtml(),
                    ]);
                }

                return JsonSuccessResponse::create('Быстрый заказ успешно создан', 200, [],
                    ['redirect' => '/cart/successFastOrder.php']);
            }
        } catch (ArgumentOutOfRangeException|ArgumentTypeException|ArgumentException $e) {
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка параметров - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        } catch (DeliveryNotAvailableException $e) {
            return $this->ajaxMess->getOrderCreateError('Доставка выбранных позиций в вашем регионе недоступна, пожалуйста попробуйте заказать другие товары или дождитесь появления данных товаров в вашем регионе');
        } catch (OrderCreateException $e) {
            return $this->ajaxMess->getOrderCreateError('Оформление быстрого заказа невозможно, пожалуйста обратитесь к администратору или попробуйте полный процесс оформления');
        } catch (NotImplementedException|NotSupportedException|ObjectNotFoundException|\Exception $e) {
            $logger = LoggerFactory::create('system');
            $logger->error('Системная ошибка - ' . $e->getMessage());
            return $this->ajaxMess->getSystemError();
        }

        return $this->ajaxMess->getOrderCreateError();
    }
}
