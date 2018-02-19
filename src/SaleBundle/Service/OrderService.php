<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\ShipmentCollection;
use Bitrix\Sale\ShipmentItemCollection;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Exception\NotFoundException as AddressNotFoundException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserRegistrationProviderInterface;

class OrderService
{

    const PAYMENT_CASH = 'cash';

    const PAYMENT_CARD = 'card';

    const PAYMENT_ONLINE = 'card-online';

    const PAYMENT_INNER = 'inner';

    const PROPERTY_TYPE_ENUM = 'ENUM';

    /**
     * Дефолтный статус заказа при курьерской доставке
     */
    const STATUS_NEW_COURIER = 'Q';

    /**
     * Дефолтный статус заказа при самовывозе
     */
    const STATUS_NEW_PICKUP = 'N';

    /**
     * 90% заказа можно оплатить бонусами
     */
    const MAX_BONUS_PAYMENT = 0.9;

    /**
     * @var AddressService
     */
    protected $addressService;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var CalculationResult[]
     */
    protected $deliveries;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var UserCitySelectInterface
     */
    protected $userCityProvider;

    /**
     * @var UserRegistrationProviderInterface
     */
    protected $userRegistrationProvider;

    /**
     * @var UserAccountService
     */
    protected $userAccountService;

    /**
     * OrderService constructor.
     *
     * @param AddressService $addressService
     * @param BasketService $basketService
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param DeliveryService $deliveryService
     * @param OrderStorageService $orderStorageService
     * @param UserCitySelectInterface $userCityProvider
     * @param UserRegistrationProviderInterface $userRegistrationProvider
     * @param UserAccountService $userAccountService
     */
    public function __construct(
        AddressService $addressService,
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider,
        DeliveryService $deliveryService,
        OrderStorageService $orderStorageService,
        UserCitySelectInterface $userCityProvider,
        UserRegistrationProviderInterface $userRegistrationProvider,
        UserAccountService $userAccountService
    ) {
        $this->addressService = $addressService;
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
        $this->deliveryService = $deliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->userCityProvider = $userCityProvider;
        $this->userRegistrationProvider = $userRegistrationProvider;
        $this->userAccountService = $userAccountService;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * Получение заказа по id
     *
     * @param int $id id заказа
     * @param bool $check выполнять ли проверки
     * @param int $userId id пользователя, к которому привязан заказ
     * @param string $hash хеш заказа (проверяется, если не передан userId)
     *
     * @throws NotFoundException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     * @return Order
     */
    public function getOrderById(int $id, bool $check = false, int $userId = null, string $hash = null): Order
    {
        if (!$order = Order::load($id)) {
            throw new NotFoundException('Order not found');
        }

        if ($check) {
            if (!$hash && !$userId) {
                throw new NotFoundException('Order not found');
            }
            if ($hash && $order->getHash() !== $hash) {
                throw new NotFoundException('Order not found');
            }

            if ($userId && (int)$order->getUserId() !== $userId) {
                throw new NotFoundException('Order not found');
            }
        }

        return $order;
    }

    /**
     * @param OrderStorage $storage
     *
     * @throws \Exception
     * @throws OrderCreateException
     * @throws NotFoundException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws BitrixRuntimeException
     * @throws ValidationException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return Order
     */
    public function createOrder(OrderStorage $storage): Order
    {
        $order = Order::create(SITE_ID);
        $selectedCity = $this->userCityProvider->getSelectedCity();

        /**
         * Привязываем корзину
         */
        /** @noinspection PhpParamsInspection */
        $order->setBasket($this->basketService->getBasket()->getOrderableItems());

        /**
         * Задание способов оплаты
         */
        if ($storage->getPaymentId()) {
            $paymentCollection = $order->getPaymentCollection();
            $sum = $order->getBasket()->getOrderableItems()->getPrice();

            if ($storage->getBonus()) {
                $innerPayment = $paymentCollection->getInnerPayment();
                $innerPayment->setField('SUM', $storage->getBonus());
                $sum -= $storage->getBonus();
            }

            $extPayment = $paymentCollection->createItem();
            $extPayment->setField('SUM', $sum);
            $extPayment->setField('PAY_SYSTEM_ID', $storage->getPaymentId());

            /** @var \Bitrix\Sale\PaySystem\Service $paySystem */
            $paySystem = $extPayment->getPaySystem();
            $extPayment->setField('PAY_SYSTEM_NAME', $paySystem->getField('NAME'));
        } else {
            throw new OrderCreateException('Не выбран способ оплаты');
        }

        /**
         * Задание способов доставки
         */
        if ($storage->getDeliveryId()) {
            $locationProp = $order->getPropertyCollection()->getDeliveryLocation();
            if (!$locationProp) {
                throw new OrderCreateException('Отсутствует свойство привязки к местоположению');
            }
            $locationProp->setValue($selectedCity['CODE']);

            $shipmentCollection = $order->getShipmentCollection();
            $shipment = $shipmentCollection->createItem();
            $shipmentItemCollection = $shipment->getShipmentItemCollection();
            $shipment->setField('CURRENCY', $order->getCurrency());
            /** @var BasketItem $item */
            foreach ($order->getBasket() as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setQuantity($item->getQuantity());
            }
            /** @var ShipmentCollection $shipmentCollection */
            $shipmentCollection = $shipment->getCollection();
            $deliveries = $this->getDeliveries();
            $selectedDelivery = null;
            /** @var CalculationResult $delivery */
            foreach ($deliveries as $delivery) {
                if ($storage->getDeliveryId() === (int)$delivery->getData()['DELIVERY_ID']) {
                    $selectedDelivery = $delivery;
                }
            }

            if (null === $selectedDelivery) {
                throw new OrderCreateException('Не выбрана доставка');
            }

            if ($this->deliveryService->isDelivery($delivery)) {
                $order->setFieldNoDemand('STATUS_ID', static::STATUS_NEW_COURIER);
            }

            $shipment->setFields(
                [
                    'DELIVERY_ID'   => $selectedDelivery->getData()['DELIVERY_ID'],
                    'DELIVERY_NAME' => $selectedDelivery->getData()['DELIVERY_NAME'],
                    'CURRENCY'      => $order->getCurrency(),
                ]
            );

            $shipmentCollection->calculateDelivery();
        } else {
            throw new OrderCreateException('Не выбрана доставка');
        }

        /**
         * Обработка полей заказа
         */
        if ($storage->getComment()) {
            $order->setField('USER_DESCRIPTION', $storage->getComment());
        }

        $address = null;
        if ($storage->getAddressId()) {
            try {
                $address = $this->addressService->getById($storage->getAddressId());
                $storage->setStreet($address->getStreet())
                        ->setHouse($address->getHouse())
                        ->setBuilding($address->getHousing())
                        ->setFloor($address->getFloor())
                        ->setApartment($address->getFlat())
                        ->setPorch($address->getEntrance());
            } catch (AddressNotFoundException $e) {
            }
        }

        /**
         * Обработка свойств заказа
         */
        $propertyValueCollection = $order->getPropertyCollection();
        $arrayStorage = $this->orderStorageService->storageToArray($storage);
        /** @var OrderProperty $orderProperty */

        $deliveryDate = $this->deliveryService->getStockResultByDelivery($selectedDelivery)
                                              ->getDeliveryDate();

        /** @var PropertyValue $propertyValue */
        foreach ($propertyValueCollection as $propertyValue) {
            $code = $propertyValue->getProperty()['CODE'];
            $key = 'PROPERTY_' . $code;

            if (!empty($arrayStorage[$key])) {
                $value = $arrayStorage[$key];
            } else {
                switch ($code) {
                    case 'DELIVERY_PLACE_CODE':
                        if (!$this->deliveryService->isInnerPickup($selectedDelivery)) {
                            continue 2;
                        }
                        $value = $storage->getDeliveryPlaceCode();
                        break;
                    case 'DPD_TERMINAL_CODE':
                        if (!$this->deliveryService->isDpdPickup($selectedDelivery)) {
                            continue 2;
                        }
                        $value = $storage->getDeliveryPlaceCode();
                        break;
                    case 'DELIVERY_DATE':
                        /**
                         * У доставок есть выбор даты доставки
                         */
                        $date = clone $deliveryDate;
                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                            if (($days = $storage->getDeliveryDate() - 1) >= 0) {
                                $date->modify('+' . $days . ' days');
                            }
                        }
                        $value = $date->format('d.m.Y');
                        break;
                    case 'DELIVERY_INTERVAL':
                        /**
                         * У доставок есть выбор интервала доставки
                         */
                        if ($this->deliveryService->isDelivery($selectedDelivery)) {
                            if (($index = $storage->getDeliveryInterval() - 1) < 0) {
                                continue 2;
                            }
                            if (!$interval = $selectedDelivery->getData()['INTERVALS'][$index]) {
                                continue 2;
                            }

                            $value = sprintf(
                                '%s:00-%s:00',
                                str_pad($interval['FROM'], 2, '0', STR_PAD_LEFT),
                                str_pad($interval['TO'], 2, '0', STR_PAD_LEFT)
                            );
                        } else {
                            $value = sprintf(
                                '%s:00-23:59',
                                $deliveryDate->format('H')
                            );
                        }

                        break;
                    default:
                        continue 2;
                }
            }

            $propertyValue->setValue($value);
        }

        /**
         * Три ситуации:
         * 1) Если юзер авторизован, то привязываем заказ к нему
         * 2) Если не авторизован, но телефон совпадает с телефоном существующего пользователя,
         *    то заказ привязывается к этому пользователю. Авторизации не происходит
         * 3) Если не авторизован, а номер телефона отсутствует в базе,
         *    то происходит регистрация. Авторизации не происходит
         */
        $needCreateAddress = false;
        $addressUserId = null;
        if ($storage->getUserId()) {
            $order->setFieldNoDemand('USER_ID', $storage->getUserId());
            $user = $this->currentUserProvider->getCurrentUser();
            if (!$user->getDiscountCardNumber() && $storage->getDiscountCardNumber()) {
                $this->currentUserProvider->getUserRepository()->updateDiscountCard(
                    $user->getId(),
                    $storage->getDiscountCardNumber()
                );
            }
            if (!$user->getEmail() && $storage->getEmail()) {
                $user->setEmail($storage->getEmail());
                $this->currentUserProvider->getUserRepository()->updateEmail(
                    $user->getId(),
                    $storage->getEmail()
                );
            }
            if (!$address) {
                $needCreateAddress = true;
                $addressUserId = $storage->getUserId();
            }
        } else {
            $users = $this->currentUserProvider->getUserRepository()->findBy(
                ['PERSONAL_PHONE' => $storage->getPhone()]
            );
            if ($user = reset($users)) {
                $order->setFieldNoDemand('USER_ID', $user->getId());
            } else {
                $user = (new User())->setName($storage->getName())
                                    ->setActive(true)
                                    ->setEmail($storage->getEmail())
                                    ->setLogin($storage->getPhone())
                                    ->setPassword(randString(6))
                                    ->setPersonalPhone($storage->getPhone());
                $this->userRegistrationProvider->register($user);
                $order->setFieldNoDemand('USER_ID', $user->getId());
                $addressUserId = $user->getId();
                $needCreateAddress = true;
            }
        }

        /**
         * Сохраняем адрес, если:
         * 1) пользователь только что зарегистрирован
         * 2) авторизованный пользователь задал новый адрес
         */
        if ($needCreateAddress) {
            $address = (new Address())->setCity($storage->getCity())
                                      ->setCityLocation($storage->getCityCode())
                                      ->setUserId($addressUserId)
                                      ->setStreet($storage->getStreet())
                                      ->setHouse($storage->getHouse())
                                      ->setHousing($storage->getBuilding())
                                      ->setEntrance($storage->getPorch())
                                      ->setFloor($storage->getFloor())
                                      ->setFlat($storage->getApartment());

            $this->addressService->add($address);
        }
        $order->doFinalAction(true);

        $result = $order->save();
        if (!$result->isSuccess()) {
            throw new OrderCreateException(implode(', ', $result->getErrorMessages()));
        }

        $this->orderStorageService->clearStorage($storage);

        return $order;
    }

    /**
     * Получение максимального кол-ва бонусов, которыми можно оплатить заказ
     *
     * @param OrderStorage $storage
     *
     * @return float
     */
    public function getMaxBonusesForPayment(OrderStorage $storage): float
    {
        if (!$storage->getUserId()) {
            return 0;
        }

        $bonuses = 0;
        try {
            $this->userAccountService->refreshUserBalance();
            $bonuses = $this->userAccountService->findAccountByUser($this->currentUserProvider->getCurrentUser())
                                                ->getCurrentBudget();
        } catch (NotFoundException $e) {
        }

        $basket = $this->basketService->getBasket()->getOrderableItems();

        return floor(min($basket->getPrice() * static::MAX_BONUS_PAYMENT, $bonuses));
    }

    /**
     * @param bool $reload
     *
     * @return CalculationResult[]
     */
    public function getDeliveries($reload = false): array
    {
        if (null === $this->deliveries || $reload) {
            $this->deliveries = $this->deliveryService->getByBasket(
                $this->basketService->getBasket()->getOrderableItems()
            );
        }

        return $this->deliveries;
    }
}
