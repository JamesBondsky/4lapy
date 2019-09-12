<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 20.06.2019
 * Time: 17:33
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\Type\DateTime as BitrixDateTime;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Error;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryVariant;
use FourPaws\MobileApiBundle\Dto\Request\OrderSubscribeRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostOrderSubscribeGoodsRequest;
use FourPaws\MobileApiBundle\Dto\Request\PostOrderSubscribeParamsRequest;
use FourPaws\MobileApiBundle\Dto\Response;
use FourPaws\MobileApiBundle\Dto\Response\OrderSubscribeListResponce;
use FourPaws\MobileApiBundle\Dto\Response\OrderSubscribeResponce;
use FourPaws\MobileApiBundle\Services\Api\OrderService;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FourPaws\PersonalBundle\Service\OrderSubscribeService as ApiOrderSubscribeService;

/**
 * Class OrderSubscribeController
 * @package FourPaws\MobileApiBundle\Controller
 * @Security("has_role('REGISTERED_USERS')")
 */
class OrderSubscribeController extends BaseController
{
    /**
     * @var ApiOrderSubscribeService
     */
    private $apiOrderSubscribeService;

    /**
     * @var DeliveryService
     */
    private $appDeliveryService;

    /**
     * @var OrderService
     */
    private $apiOrderService;

    /**
     * Mapping для служб доставки
     * @var array
     */
    private $deliveryCodes = [
        'courier' => DeliveryService::INNER_DELIVERY_CODE,
        'pickup' => DeliveryService::INNER_PICKUP_CODE,
    ];


    public function __construct(
        ApiOrderSubscribeService $apiOrderSubscribeService,
        DeliveryService $appDeliveryService,
        OrderService $apiOrderService
    )
    {
        $this->apiOrderSubscribeService = $apiOrderSubscribeService;
        $this->appDeliveryService = $appDeliveryService;
        $this->apiOrderService = $apiOrderService;
    }

    /**
     * @Rest\Get(path="/order_subscribe_list/")
     * @Rest\View()
     * @return OrderSubscribeListResponce
     * @throws \Exception
     */
    public function getOrderSubscribeListAction()
    {
        global $USER;
        $orderSubscribeCollection = $this->apiOrderSubscribeService->getSubscriptionsByUser($USER->GetId());
        return new OrderSubscribeListResponce($orderSubscribeCollection);
    }

    /**
     * @Rest\Get(path="/order_subscribe/")
     * @Rest\View()
     * @return OrderSubscribeResponce
     * @throws \Exception
     */
    public function getOrderSubscribeAction(OrderSubscribeRequest $request)
    {
        try {
            $orderSubscribe = $this->getOrderSubscribeById($request->getOrderSubscribeId());
            $responce = new OrderSubscribeResponce($orderSubscribe);
        } catch (\Exception $e) {
            $responce = new Response([]);
            $responce->addError(new Error(1, sprintf('Не удалось получить информацию о подписке на доставку [%s]', $e->getMessage())));
        }

        return $responce;
    }

    /**
     * @Rest\Post(path="/order_subscribe_edit_goods/")
     * @Rest\View()
     * @return OrderSubscribeResponce
     * @throws \Exception
     */
    public function orderSubscribeEditGoodsAction(PostOrderSubscribeGoodsRequest $request)
    {
        $orderSubscribe = $this->getOrderSubscribeById($request->getOrderSubscribeId());

        $goods = [];
        foreach($request->getGoods() as $orderSubscribeItem){
            $apiOrderSubscribeItem = (new OrderSubscribeItem())
                ->setSubscribeId($orderSubscribe->getId())
                ->setOfferId($orderSubscribeItem->getOfferId())
                ->setQuantity($orderSubscribeItem->getQuantity())
            ;

            $goods[] = $apiOrderSubscribeItem;
        }

        $this->apiOrderSubscribeService->deleteAllItems($orderSubscribe->getId());
        foreach ($goods as $orderSubscribeItem) {
            $this->apiOrderSubscribeService->addSubscribeItem($orderSubscribe, $orderSubscribeItem);
        }

        $responce = new OrderSubscribeResponce($this->apiOrderSubscribeService->getById($request->getOrderSubscribeId()));
        return $responce;
    }

    /**
     * @Rest\Get(path="/order_subscribe_get_params/")
     * @Rest\View()
     * @param OrderSubscribeRequest $request
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     */
    public function orderSubscribeGetParamsAction(OrderSubscribeRequest $request)
    {
        $orderSubscribe = $this->getOrderSubscribeById($request->getOrderSubscribeId());

        $return = null;
        $step = 2;
        $subscribeId = $orderSubscribe->getId();
        $orderId = $orderSubscribe->getOrderId();

        /** @var \FourPawsPersonalCabinetOrdersSubscribeFormComponent $component */
        $component = $GLOBALS['APPLICATION']->IncludeComponent(
            'fourpaws:personal.orders.subscribe.form',
            'popup',
            [
                'INCLUDE_TEMPLATE' => 'N',
                'STEP' => $step,
                'SUBSCRIBE_ID' => $subscribeId,
                'ORDER_ID' => $orderId,
            ],
            null,
            [
                'HIDE_ICONS' => 'Y',
            ]
        );

        $arResult = $component->arResult;

        $courierDelivery = (new DeliveryVariant());
        $pickupDelivery = (new DeliveryVariant());
        $deliveryDates = [];

        if ($delivery = $arResult['DELIVERY']) {
            $courierDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime($delivery));

            $nextDeliveries = $this->appDeliveryService->getNextDeliveries($delivery, 10);
            foreach($nextDeliveries as $nextDelivery){
                $date = [
                    'weekday' => FormatDate('l', $nextDelivery->getDeliveryDate()->getTimestamp()),
                    'date' => FormatDate('d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()),
                ];
                $avaliableIntervals = $nextDelivery->getAvailableIntervals();
                foreach($avaliableIntervals as $interval){
                    $date['time'][] = (string)$interval;
                }
                $deliveryDates[] = $date;
            }
        }
        if ($pickup = $arResult['PICKUP']) {
            $pickupDelivery
                ->setAvailable(true)
                ->setDate(DeliveryTimeHelper::showTime(
                    $pickup,
                    [
                        'SHOW_TIME' => !$this->appDeliveryService->isDpdPickup($pickup),
                    ]
                ));
        }

        $frequency = $this->apiOrderService->getSubscribeFrequencies();

        $data = [
          'courier' => $courierDelivery,
          'pickup'  => $pickupDelivery,
          'deliveryDates' => $deliveryDates,
          'frequency' => $frequency,

        ];

        return new Response($data);
    }

    /**
     * @Rest\Post(path="/order_subscribe_edit_params/")
     * @Rest\View()
     * @param PostOrderSubscribeParamsRequest $request
     * @return OrderSubscribeResponce
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \Exception
     */
    public function orderSubscribeEditParamsAction(PostOrderSubscribeParamsRequest $request)
    {
        try {
            $orderSubscribe = $this->getOrderSubscribeById($request->getOrderSubscribeId());
            $deliveryDate = new BitrixDateTime($request->getDeliveryDate());
            $deliveryId = $this->getDeliveryIdByCode($request->getDeliveryCode());

            $orderSubscribe
                ->setDeliveryId($deliveryId)
                ->setDeliveryPlace($request->getDeliveryPlace())
                ->setNextDate($deliveryDate)
                ->setDeliveryTime($request->getDeliveryTime())
                ->setFrequency($request->getFrequency())
                ->setPayWithbonus($request->getPayWithBonus())
            ;

            $this->apiOrderSubscribeService->update($orderSubscribe);
            $responce = new OrderSubscribeResponce($this->apiOrderSubscribeService->getById($request->getOrderSubscribeId()));
        } catch (\Exception $e) {
            $responce = new Response([]);
            $responce->addError(new Error(1, sprintf('Не удалось обновить информацию о подписке на доставку [%s]', $e->getMessage())));
        }

        return $responce;
    }

    /**
     * @Rest\Post(path="/order_subscribe_renewal/")
     * @Rest\View()
     * @param PostOrderSubscribeParamsRequest $request
     * @return OrderSubscribeResponce
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \Exception
     */
    public function orderSubscribeRenewal(OrderSubscribeRequest $request)
    {
        $orderSubscribe = $this->getOrderSubscribeById($request->getOrderSubscribeId());
        $deliveryTime = new BitrixDateTime($request->getDeliveryTime());
        $deliveryId = $this->getDeliveryIdByCode($request->getDeliveryCode());

        $orderSubscribe
            ->setDeliveryId($deliveryId)
            ->setDeliveryPlace($request->getDeliveryPlace())
            ->setNextDate($deliveryTime)
            ->setDeliveryTime($request->getDeliveryTime())
            ->setFrequency($request->getFrequency())
            ->setPayWithbonus($request->getPayWithBonus())
            ->setActive(true)
            ->countDateCheck()
        ;

        $this->apiOrderSubscribeService->update($orderSubscribe);
        $responce = new OrderSubscribeResponce($this->apiOrderSubscribeService->getById($request->getOrderSubscribeId()));
        return $responce;
    }

    /**
     * @Rest\Post(path="/order_subscribe_cancel/")
     * @Rest\View()
     * @param OrderSubscribeRequest $request
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\PersonalBundle\Exception\BitrixOrderNotFoundException
     * @throws \FourPaws\PersonalBundle\Exception\NotFoundException
     */
    public function orderSubscribeCancel(OrderSubscribeRequest $request)
    {
        $result = $this->apiOrderSubscribeService->deactivateSubscription($this->getOrderSubscribeById($request->getOrderSubscribeId()));
        return new Response(['success' => $result->isSuccess()]);
    }

    /**
     * @param int $orderSubscribeId
     * @return OrderSubscribe
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\AppBundle\Exception\NotFoundException
     * @throws \Exception
     */
    protected function getOrderSubscribeById(int $orderSubscribeId)
    {
        global $USER;
        $userId = $USER->GetId();
        /** @var OrderSubscribe $orderSubscribe */
        $orderSubscribe = $this->apiOrderSubscribeService->getById($orderSubscribeId);
        if($userId != $orderSubscribe->getUserId()){
            throw new \Exception("Вы не можете редактировать подписки на доставку для другого пользователя");
        }

        return $orderSubscribe;
    }

    /**
     * @param int $deliveryId
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \FourPaws\DeliveryBundle\Exception\NotFoundException
     * @throws \Exception
     */
    protected function getDeliveryIdByCode(string $deliveryId)
    {
        $deliveryCode = $this->deliveryCodes[$deliveryId];
        if(!$deliveryCode){
            throw new \Exception(sprintf('Указан неверный тип службы доставки: %s', $deliveryId));
        }
        return $this->appDeliveryService->getDeliveryIdByCode($deliveryCode);
    }


}
