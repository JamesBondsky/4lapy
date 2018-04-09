<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\ValidationException;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SaleBundle\Service\UserAccountService;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Exception\NotAuthorizedException;

/** @noinspection AutoloadingIssuesInspection */
class FourPawsOrderCompleteComponent extends \CBitrixComponent
{
    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

    /** @var DeliveryService */
    protected $deliveryService;

    /** @var OrderService */
    protected $orderService;

    /** @var StoreService */
    protected $storeService;

    /** @var ManzanaPosService */
    protected $manzanaPosService;

    /** @var UserAccountService */
    protected $userAccountService;

    /**
     * FourPawsOrderCompleteComponent constructor.
     * @param null $component
     * @throws ApplicationCreateException
     */
    public function __construct($component = null)
    {
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->currentUserProvider = $serviceContainer->get(CurrentUserProviderInterface::class);
        $this->storeService = $serviceContainer->get('store.service');
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->manzanaPosService = $serviceContainer->get('manzana.pos.service');
        $this->userAccountService = $serviceContainer->get(UserAccountService::class);

        parent::__construct($component);
    }

    /** {@inheritdoc} */
    public function executeComponent()
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;
        try {
            $this->prepareResult();

            if ($this->arParams['SET_TITLE'] === 'Y') {
                $APPLICATION->SetTitle('Заказ оформлен');
            }

            $this->includeComponentTemplate();
        } catch (\Exception $e) {
            try {
                $logger = LoggerFactory::create('component');
                $logger->error(sprintf('Component execute error: %s', $e->getMessage()));
            } catch (\RuntimeException $e) {
            }
        }
    }

    /**
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     * @throws ExecuteException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws ValidationException
     *
     * @return $this
     */
    protected function prepareResult()
    {
        $user = null;
        $isAuthorized = false;
        $order = null;
        $relatedOrder = null;
        try {
            $user = $this->currentUserProvider->getCurrentUser();
            $isAuthorized = true;
        } catch (NotAuthorizedException $e) {
        }
        /**
         * При переходе на страницу "спасибо за заказ" мы ищем заказ с переданным id
         */
        try {
            $order = $this->orderService->getOrderById(
                $this->arParams['ORDER_ID'],
                true,
                $user ? $user->getId() : null,
                $this->arParams['HASH']
            );
            if ($this->orderService->hasRelatedOrder($order)) {
                $relatedOrder = $this->orderService->getRelatedOrder($order);
                if ($relatedOrder->getId() < $order->getId()) {
                    $tmp = $relatedOrder;
                    $relatedOrder = $order;
                    $order = $tmp;
                }
            }
        } catch (NotFoundException $e) {
            Tools::process404('', true, true, true);
        }

        /**
         * Попытка открыть уже обработанный заказ
         */
        if (!\in_array(
            $order->getField('STATUS_ID'),
            [
                OrderService::STATUS_NEW_COURIER,
                OrderService::STATUS_NEW_PICKUP,
            ],
            true
        )
        ) {
            Tools::process404('', true, true, true);
        }

        if (!$user) {
            $user = $this->currentUserProvider->getUserRepository()->find($order->getUserId());
        }

        $this->arResult['ORDER'] = $order;
        $this->arResult['ORDER_PROPERTIES'] = $this->getOrderProperties($order, $user);
        /**
         * флаг, что пользователь был зарегистрирован при оформлении заказа
         */
        $this->arResult['ORDER_REGISTERED'] = !$isAuthorized;

        if (null !== $relatedOrder) {
            $this->arResult['RELATED_ORDER'] = $relatedOrder;
            $this->arResult['RELATED_ORDER_PROPERTIES'] = $this->getOrderProperties($relatedOrder, $user);
            $this->arResult['RELATED_ORDER_DELIVERY'] = $this->getDeliveryData(
                $relatedOrder,
                $this->arResult['RELATED_ORDER_PROPERTIES']
            );
        }
        $this->userAccountService->refreshUserBalance($user);

        /** @var Shipment $shipment */
        if ($shipment = $order->getShipmentCollection()->current()) {
            $this->arResult['ORDER_DELIVERY'] = $this->getDeliveryData($order, $this->arResult['ORDER_PROPERTIES']);
            $deliveryCode = $shipment->getDelivery()->getCode();
            $this->arResult['ORDER_DELIVERY']['DELIVERY_CODE'] = $deliveryCode;
            $this->arResult['ORDER_DELIVERY']['IS_PICKUP'] = in_array(
                $deliveryCode,
                DeliveryService::PICKUP_CODES,
                true
            );
            $this->arResult['ORDER_DELIVERY']['IS_DPD_PICKUP'] = $deliveryCode === DeliveryService::DPD_PICKUP_CODE;
            $this->arResult['ORDER_DELIVERY']['IS_DPD_DELIVERY'] = $deliveryCode === DeliveryService::DPD_DELIVERY_CODE;
            if ($this->arResult['ORDER_PROPERTIES']['DPD_TERMINAL_CODE']) {
                $this->arResult['ORDER_DELIVERY']['SELECTED_SHOP'] = $this->deliveryService->getDpdTerminalByCode(
                    $this->arResult['ORDER_PROPERTIES']['DPD_TERMINAL_CODE']
                );
            } elseif ($this->arResult['ORDER_PROPERTIES']['DELIVERY_PLACE_CODE']) {
                $this->arResult['ORDER_DELIVERY']['SELECTED_SHOP'] = $this->storeService->getByXmlId(
                    $this->arResult['ORDER_PROPERTIES']['DELIVERY_PLACE_CODE']
                );
            }
        }

        return $this;
    }

    /**
     * @param Order $order
     * @param User $user
     *
     * @throws ArgumentException
     * @throws Exception
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws ExecuteException
     * @return array
     */
    protected function getOrderProperties(Order $order, User $user): array
    {
        $result = [];
        /** @var PropertyValue $propertyValue */
        foreach ($order->getPropertyCollection() as $propertyValue) {
            $propertyCode = $propertyValue->getProperty()['CODE'];
            /**
             * У юзера есть бонусная карта, а бонусы за заказ еще не начислены.
             */
            if ($user->getDiscountCardNumber() &&
                ($propertyCode === 'BONUS_COUNT') &&
                (null === $propertyValue->getValue())
            ) {
                if ($order->getPaymentCollection()->getInnerPayment()->getSum()) {
                    $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                        $this->manzanaPosService->buildRequestFromBasket(
                            $order->getBasket(),
                            $user->getDiscountCardNumber()
                        )
                    );

                    $propertyValue->setValue($cheque->getChargedBonus());
                } else {
                    $propertyValue->setValue(0);
                }
                $order->save();
            }
            $result[$propertyCode] = $propertyValue->getValue();
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param array $properties
     *
     * @throws ArgumentException
     * @return array
     */
    protected function getDeliveryData(Order $order, array $properties): array
    {
        $result = [];
        $result['ADDRESS'] = $this->orderService->getOrderDeliveryAddress($order);
        if ($properties['DPD_TERMINAL_CODE']) {
            $terminals = $this->deliveryService->getDpdTerminalsByLocation($properties['CITY_CODE']);
            /** @var Store $terminal */
            if ($terminal = $terminals[$properties['DPD_TERMINAL_CODE']]) {
                $result['SCHEDULE'] = $terminal->getScheduleString();
            }
        } elseif ($properties['DELIVERY_PLACE_CODE']) {
            try {
                $store = $this->storeService->getByXmlId($properties['DELIVERY_PLACE_CODE']);
                $result['SCHEDULE'] = $store->getScheduleString();
            } catch (StoreNotFoundException $e) {
            }
        }

        if ($properties['DELIVERY_DATE']) {
            $match = [];
            $deliveryString = $properties['DELIVERY_DATE'];
            if (preg_match('~^(\d{2}):\d{2}~', $properties['DELIVERY_INTERVAL'], $match)) {
                $deliveryString .= ' ' . $match[1] . ':00';
            } else {
                $deliveryString .= ' 00:00';
            }

            $result['DELIVERY_DATE'] = \DateTime::createFromFormat('d.m.Y H:i', $deliveryString);
        }

        if ($properties['DELIVERY_INTERVAL']) {
            $result['DELIVERY_INTERVAL'] = $properties['DELIVERY_INTERVAL'];
        }

        return $result;
    }
}
