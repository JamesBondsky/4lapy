<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Delivery\DeliveryLocationTable;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Delivery\Services\Table as DeliveryServiceTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\PriceForAmountCollection;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Dpd\TerminalTable;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\DeliveryResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\ExpressDeliveryResult;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResult;
use FourPaws\DeliveryBundle\Entity\PriceForAmount;
use FourPaws\DeliveryBundle\Entity\Terminal;
use FourPaws\DeliveryBundle\Exception\DeliveryInitializeException;
use FourPaws\DeliveryBundle\Exception\LocationNotFoundException;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Exception\TerminalNotFoundException;
use FourPaws\DeliveryBundle\Exception\UnknownDeliveryException;
use FourPaws\DeliveryBundle\Factory\CalculationResultFactory;
use FourPaws\DeliveryBundle\Handler\DeliveryHandlerBase;
use FourPaws\Helpers\WordHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Discount\Utils\Manager as DiscountManager;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use Psr\Log\LoggerAwareInterface;
use WebArch\BitrixCache\BitrixCache;
use FourPaws\App\Application;

/**
 * Class DeliveryService
 *
 * @package FourPaws\DeliveryBundle\Service
 */
class DeliveryService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    public const INNER_DELIVERY_CODE = '4lapy_delivery';
    public const DELIVERY_DOSTAVISTA_CODE = 'dostavista';
    public const DOBROLAP_DELIVERY_CODE = 'dobrolap_delivery';
    public const INNER_PICKUP_CODE = '4lapy_pickup';
    public const EXPRESS_DELIVERY_CODE = '4lapy_express';
    public const DPD_DELIVERY_GROUP_CODE = 'ipolh_dpd';
    public const DPD_DELIVERY_CODE = self::DPD_DELIVERY_GROUP_CODE . ':COURIER';
    public const DPD_PICKUP_CODE = self::DPD_DELIVERY_GROUP_CODE . ':PICKUP';
    public const ORDER_LOCATION_PROP_CODE = 'CITY_CODE';

    public const LOCATION_RESTRICTION_TYPE_LOCATION = 'L';
    public const LOCATION_RESTRICTION_TYPE_GROUP = 'G';

    public const ZONE_1 = 'ZONE_1';
    public const ZONE_2 = 'ZONE_2';
    public const ZONE_3 = 'ZONE_3';
    public const ZONE_4 = 'ZONE_4';
    public const ZONE_5 = 'ZONE_5';
    public const ZONE_6 = 'ZONE_6';

    public const MOSCOW_LOCATION_CODE = '0000073738';
    public const MOSCOW_LOCATION_NAME = 'Москва';

    /**
     * Нижний Новгород и Нижегородская область
     */
    public const ZONE_NIZHNY_NOVGOROD = 'ZONE_NIZHNY_NOVGOROD';
    public const ZONE_NIZHNY_NOVGOROD_REGION = 'ZONE_NIZHNY_NOVGOROD_REGION';
    /**
     * Владимир и Владимирская область
     */
    public const ZONE_VLADIMIR = 'ZONE_VLADIMIR';
    public const ZONE_VLADIMIR_REGION = 'ZONE_VLADIMIR_REGION';
    /**
     * Воронеж и Воронежская область
     */
    public const ZONE_VORONEZH = 'ZONE_VORONEZH';
    public const ZONE_VORONEZH_REGION = 'ZONE_VORONEZH_REGION';
    /**
     * Ярославль и Ярославская область
     */
    public const ZONE_YAROSLAVL = 'ZONE_YAROSLAVL';
    public const ZONE_YAROSLAVL_REGION = 'ZONE_YAROSLAVL_REGION';
    /**
     * Тула и Тульская область
     */
    public const ZONE_TULA = 'ZONE_TULA';
    public const ZONE_TULA_REGION = 'ZONE_TULA_REGION';
    /**
     * Калуга и Калужская область
     */
    public const ZONE_KALUGA = 'ZONE_KALUGA';
    public const ZONE_KALUGA_REGION = 'ZONE_KALUGA_REGION';
    /**
     * Иваново и Ивановская область
     */
    public const ZONE_IVANOVO = 'ZONE_IVANOVO';
    public const ZONE_IVANOVO_REGION = 'ZONE_IVANOVO_REGION';
    /**
     * Зона Москва для Достависты
     */
    public const ZONE_MOSCOW = 'ZONE_MOSCOW';

    /**
     * Новые зоны с префиксом, работают как зона 2
     */
    public const ADD_DELIVERY_ZONE_CODE_PATTERN = 'ADD_DELIVERY_ZONE_';

    /**
     * Зоны Москвы
     */
    public const ADD_DELIVERY_ZONE_10 = 'ADD_DELIVERY_ZONE_10';

    /**
     * Исключения для DPD
     */
    public const ZONE_DPD_EXCLUDE = 'ZONE_DPD_EXCLUDE';

    /**
     * Новые зоны - районы Москвы
     */
    public const ZONE_MOSCOW_DISTRICT_CODE_PATTERN = 'ZONE_MOSCOW_DISTRICT_';

    public const ZONE_EXPRESS_DELIVERY_45 = 'ZONE_EXPRESS_DELIVERY_45';
    public const ZONE_EXPRESS_DELIVERY_90 = 'ZONE_EXPRESS_DELIVERY_90';

    public const ZONE_EXPRESS_DELIVERY = [
        DeliveryService::ZONE_EXPRESS_DELIVERY_45,
        DeliveryService::ZONE_EXPRESS_DELIVERY_90,
    ];

    public const PICKUP_CODES = [
        DeliveryService::INNER_PICKUP_CODE,
        DeliveryService::DPD_PICKUP_CODE,
    ];

    public const DELIVERY_CODES = [
        DeliveryService::INNER_DELIVERY_CODE,
        DeliveryService::DPD_DELIVERY_CODE,
        DeliveryService::DELIVERY_DOSTAVISTA_CODE,
        DeliveryService::DOBROLAP_DELIVERY_CODE,
        DeliveryService::EXPRESS_DELIVERY_CODE
    ];

    /** @var array */
    public static $dpdData = [];

    /**
     * @var LocationService $locationService
     */
    protected $locationService;

    /** @var string */
    protected $currentDeliveryZone;

    protected $allZones;

    protected $deliveryByZoneMap;

    /**
     * DeliveryService public constructor.
     *
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
        $this->withLogName('DeliveryService');
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * Получение доставок для товара
     *
     * @param Offer          $offer
     * @param string         $locationCode
     * @param array          $codes
     * @param \DateTime|null $from
     *
     *
     * @return CalculationResultInterface[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws NotImplementedException
     * @throws ObjectException
     * @throws \Exception
     */
    public function getByProduct(
        Offer $offer,
        string $locationCode = '',
        array $codes = [],
        ?\DateTime $from = null
    ): array
    {
        $basket = Basket::createFromRequest([]);
        $basketItem = BasketItem::create($basket, 'sale', $offer->getId());
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('CAN_BUY', 'Y');
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('PRICE', $offer->getPrice());
        /** @noinspection PhpInternalEntityUsedInspection */
        $basketItem->setFieldNoDemand('QUANTITY', 1);
        /** @noinspection PhpInternalEntityUsedInspection */
        $basket->addItem($basketItem);

        return $this->getByBasket($basket, $locationCode, $codes, $from);
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param OfferCollection $offers
     * @param array $quantities
     * @param string $locationCode
     * @param array $codes
     * @param \DateTime|null $from
     * @return array
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     */
    public function getByOfferCollection(OfferCollection $offers, array $quantities, string $locationCode = '', array $codes = [], ?\DateTime $from = null): array
    {
        $basket = Basket::createFromRequest([]);
        /** @var Offer $offer */
        foreach ($offers as $offer) {
            $basketItem = BasketItem::create($basket, 'sale', $offer->getId());
            $basketItem->setFieldNoDemand('CAN_BUY', 'Y');
            $basketItem->setFieldNoDemand('PRICE', $offer->getPrice());
            $basketItem->setFieldNoDemand('QUANTITY', $quantities[$offer->getXmlId()]);
            $basket->addItem($basketItem);
        }

        if (!$locationCode) {
            $locationCode = $this->locationService->getCurrentLocation();
        }

        $shipment = $this->generateShipment($locationCode, $basket);

        $isToEnableExtendDiscount = DiscountManager::isExtendDiscountEnabled();

        if ($isToEnableExtendDiscount) {
            DiscountManager::disableExtendsDiscount();
            $isToEnableExtendDiscount = true;
        }

        $availableServices = Manager::getRestrictedObjectsList($shipment);

        $result = [];
        $errors = [];
        $location = $this->getDeliveryLocation($shipment);
        foreach ($availableServices as $service) {
            if ($codes && !\in_array($service->getCode(), $codes, true)) {
                continue;
            }

            if ($service::isProfile()) {
                $name = $service->getNameWithParent();
            } else {
                $name = $service->getName();
            }

            try {
                $shipment->setFieldsNoDemand(
                    [
                        'DELIVERY_ID'   => $service->getId(),
                        'DELIVERY_NAME' => $name,
                    ]
                );
            } catch (\Exception $e) {
                $this->log()->error(sprintf('Cannot set shipment fields: %s', $e->getMessage()), [
                    'location' => $location,
                    'service'  => $service->getCode(),
                ]);
                continue;
            }

            $calculationResult = $shipment->calculateDelivery();
            if (!$calculationResult->isSuccess()) {
                $errors[$service->getCode()] = $calculationResult->getErrorMessages();
                continue;
            }

            try {
                $calculationResult = CalculationResultFactory::fromBitrixResult($calculationResult, $service, $shipment);
            } catch (UnknownDeliveryException|DeliveryInitializeException $e) {
                $this->log()->critical($e->getMessage(), [
                    'service'  => $service->getCode(),
                    'location' => $location,
                    'trace' => $e->getTrace()
                ]);
                continue;
            }
            $deliveryZone = $this->getDeliveryZoneForShipment($shipment);
            $calculationResult->setDeliveryZone($deliveryZone);
            $calculationResult->setDeliveryId($service->getId());
            $calculationResult->setDeliveryName($name);
            $calculationResult->setDeliveryCode($service->getCode());
            $calculationResult->setCurrentDate($from ?? new \DateTime());

            //проверка, что достависта работает еще в течение 3ех часов
            if ($calculationResult->getDeliveryCode() == DeliveryService::DELIVERY_DOSTAVISTA_CODE) {
                $deliveryDate = clone $calculationResult->getDeliveryDate();
                $deliveryDateOfMonth = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что следующие сутки не наступили
                $deliveryStartTime = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что курьерская доставка сейчас работает
                $deliveryEndTime = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что курьерская доставка еще будет работать с учетом времени доставки
                //проверяем размеры товаров
                /** @var OrderService $orderService */
                $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
                $parentOrder = $shipment->getParentOrder();
                if ($parentOrder->getBasket()->isEmpty())
                {
                    /** @var BasketService $basketService */
                    $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
                    $offers = $basketService->getBasketOffers();
                }
                else
                {
                    /** @var OfferCollection $offers */
                    $offers = $orderService->getOrderProducts($parentOrder);
                }

                if (!$offers->isEmpty()) {
                    foreach ($offers as $offer) {
                        $length = WordHelper::showLengthNumber($offer->getCatalogProduct()->getLength());
                        $width = WordHelper::showLengthNumber($offer->getCatalogProduct()->getWidth());
                        $height = WordHelper::showLengthNumber($offer->getCatalogProduct()->getHeight());
                        if ($length > 150 || $width > 150 || $height > 150) {
                            $calculationResult->setPeriodTo(300);
                            break;
                        }
                    }
                }
                $deliveryDateOfMonth->modify(sprintf('+%s minutes', $calculationResult->getPeriodTo())); //прибавляем максимальное время доставки
                $startTime = $calculationResult->getData()['DELIVERY_START_TIME']; //когда доставка открывается
                $arStartTime = explode(':', $startTime);
                $deliveryStartTime->setTime($arStartTime[0], $arStartTime[1]); //получаем сегодня, когда доставка открывается
                $endTime = $calculationResult->getData()['DELIVERY_END_TIME']; //когда доставка закрывается
                $arEndTime = explode(':', $endTime);
                $deliveryEndTime->setTime($arEndTime[0], $arEndTime[1]); //получаем сегодня, когда доставка закроется
                $oldDayOfMonth = $calculationResult->getDeliveryDate()->format('d'); //получаем номер старого дня в месяце
                $newDayOfMonth = $deliveryDateOfMonth->format('d'); //получаем номер нового дня в месяце с учетом времени доставки
                if ($oldDayOfMonth != $newDayOfMonth || $deliveryDateOfMonth > $deliveryEndTime || $deliveryDate < $deliveryStartTime) {
                    continue;
                }
            }

            //тут рассчет времени доставки
            if ($calculationResult->isSuccess()) {
                $result[] = $calculationResult;
            } else {
                $errors[$calculationResult->getDeliveryCode()] = $calculationResult->getErrorMessages();
            }
        }

        if (empty($codes) && empty($result)) {
            $this->log()->info('No available deliveries', [
                'location' => $location,
                'errors' => $errors
            ]);
        }

        if ($isToEnableExtendDiscount) {
            DiscountManager::enableExtendsDiscount();
        }

        return $result;
    }

    /**
     * Получение доставок для корзины
     * @param BasketBase     $basket
     * @param string         $locationCode
     * @param array          $codes
     * @param \DateTime|null $from
     *
     * @return CalculationResultInterface[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws UserMessageException
     * @throws SystemException
     * @throws \Exception
     */
    public function getByBasket(
        BasketBase $basket,
        string $locationCode = '',
        array $codes = [],
        ?\DateTime $from = null
    ): array
    {
        if (!$locationCode) {
            $locationCode = $this->locationService->getCurrentLocation();
        }

        $shipment = $this->generateShipment($locationCode, $basket);

        return $this->calculateDeliveries($shipment, $codes, $from);
    }

    /**
     * Получение доставок для местоположения
     *
     * @param string $locationCode
     * @param array  $codes
     *
     * @return CalculationResultInterface[]
     */
    public function getByLocation(string $locationCode = '', array $codes = []): array
    {
        if (!$locationCode) {
            $locationCode = $this->locationService->getCurrentLocation();
        }

        $deliveries = [];
        $getDeliveries = function () use ($locationCode) {
            $shipment = $this->generateShipment($locationCode);

            return ['result' => $this->calculateDeliveries($shipment)];
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode)
                ->withTag('location:groups')
                ->resultOf($getDeliveries);
            $deliveries = $result['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get deliveries for location: %s', $e->getMessage()), [
                'location' => $locationCode,
            ]);
        }
        if (!empty($codes)) {
            /**
             * @var CalculationResultInterface $delivery
             */
            foreach ($deliveries as $i => $delivery) {
                if (!\in_array($delivery->getDeliveryCode(), $codes, true)) {
                    unset($deliveries[$i]);
                }
            }
        }

        return $deliveries;
    }

    /**
     * @param string $zone
     *
     * @throws ApplicationCreateException
     * @return string[]
     */
    public function getByZone(string $zone = '')
    {
        if (!$zone) {
            $zone = $this->getCurrentDeliveryZone();
        }
        if($this->deliveryByZoneMap[$zone] == null) {
            $getServiceCodes = function () use ($zone) {
                $zoneData = $this->getAllZones(true)[$zone];
                $result = [];
                if (!empty($zoneData['LOCATIONS'])) {
                    $location = current($zoneData['LOCATIONS']);
                    if (!empty($location)) {
                        $shipment = $this->generateShipment($location);
                        $availableServices = Manager::getRestrictedObjectsList($shipment);

                        foreach ($availableServices as $service) {
                            $result[] = $service->getCode();
                        }
                    }
                }

                return ['result' => $result];
            };

            $result = [];
            try {
                $result = (new BitrixCache())
                    ->withId(__METHOD__ . $zone)
                    ->withTag('location:groups')
                    ->resultOf($getServiceCodes)['result'];
            } catch (\Exception $e) {
                $this->log()->error(
                    sprintf(
                        'failed to get deliveries by zone: %s: %s',
                        \get_class($e),
                        $e->getMessage()
                    ),
                    ['zone' => $zone]
                );
            }
            $this->deliveryByZoneMap[$zone] = $result;
        }

        return $this->deliveryByZoneMap[$zone];
    }

    /**
     * Выполняет расчет всех возможных (или указанных) доставок
     *
     * @param Shipment       $shipment
     * @param array          $codes
     * @param \DateTime|null $from
     *
     * @return CalculationResultInterface[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws StoreNotFoundException
     * @throws SystemException
     */
    public function calculateDeliveries(Shipment $shipment, array $codes = [], ?\DateTime $from = null): array
    {
        $isToEnableExtendDiscount = DiscountManager::isExtendDiscountEnabled();

        if ($isToEnableExtendDiscount) {
            DiscountManager::disableExtendsDiscount();
            $isToEnableExtendDiscount = true;
        }

        $availableServices = Manager::getRestrictedObjectsList($shipment);

        $result = [];
        $errors = [];
        $location = $this->getDeliveryLocation($shipment);
        foreach ($availableServices as $service) {
            if ($codes && !\in_array($service->getCode(), $codes, true)) {
                continue;
            }

            if ($service::isProfile()) {
                $name = $service->getNameWithParent();
            } else {
                $name = $service->getName();
            }

            try {
                $shipment->setFieldsNoDemand(
                    [
                        'DELIVERY_ID'   => $service->getId(),
                        'DELIVERY_NAME' => $name,
                    ]
                );
            } catch (\Exception $e) {
                $this->log()->error(sprintf('Cannot set shipment fields: %s', $e->getMessage()), [
                    'location' => $location,
                    'service'  => $service->getCode(),
                ]);
                continue;
            }

            $calculationResult = $shipment->calculateDelivery();
            if (!$calculationResult->isSuccess()) {
                $errors[$service->getCode()] = $calculationResult->getErrorMessages();
                continue;
            }

            try {
                $calculationResult = CalculationResultFactory::fromBitrixResult($calculationResult, $service, $shipment);
            } catch (UnknownDeliveryException|DeliveryInitializeException $e) {
                $this->log()->critical($e->getMessage(), [
                    'service'  => $service->getCode(),
                    'location' => $location,
                    'trace' => $e->getTrace()
                ]);
                continue;
            }
            $deliveryZone = $this->getDeliveryZoneForShipment($shipment);
            $calculationResult->setDeliveryZone($deliveryZone);
            $calculationResult->setDeliveryId($service->getId());
            $calculationResult->setDeliveryName($name);
            $calculationResult->setDeliveryCode($service->getCode());
            $calculationResult->setCurrentDate($from ?? new \DateTime());

            //проверка, что достависта работает еще в течение 3ех часов
            if ($calculationResult->getDeliveryCode() == DeliveryService::DELIVERY_DOSTAVISTA_CODE) {
                $deliveryDate = clone $calculationResult->getDeliveryDate();
                $deliveryDateOfMonth = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что следующие сутки не наступили
                $deliveryStartTime = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что курьерская доставка сейчас работает
                $deliveryEndTime = clone $calculationResult->getDeliveryDate(); //клонируем для проверки, что курьерская доставка еще будет работать с учетом времени доставки
                //проверяем размеры товаров
                /** @var OrderService $orderService */
                $orderService = Application::getInstance()->getContainer()->get(OrderService::class);
                $parentOrder = $shipment->getParentOrder();
                $dostavistaContinue = false;
                if ($parentOrder->getBasket()->isEmpty()) {
                    /** @var BasketService $basketService */
                    $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
                    $offers = $basketService->getBasketOffers();
                    /** @var BasketItemCollection $basketItems */
                    $basketItems = $basketService->getBasket()->getBasketItems();
                } else {
                    /** @var OfferCollection $offers */
                    $offers = $orderService->getOrderProducts($parentOrder);
                    /** @var BasketItemCollection $basketItems */
                    $basketItems = $parentOrder->getBasket()->getBasketItems();
                }

                //если вес > 50 то Достависта недоступна
                $weightSumm = 0;
                /** @var BasketItem $basketItem */
                foreach($basketItems as $basketItem){
                    $weightSumm += $basketItem->getQuantity() * WordHelper::showWeightNumber((float)$basketItem->getWeight(), true);
                }

                if ($weightSumm > 50) {
                    $dostavistaContinue = true;
                }

                if (!$offers->isEmpty()) {
                    /** @var Offer $offer */
                    foreach ($offers as $offer) {
                        $length = WordHelper::showLengthNumber($offer->getCatalogProduct()->getLength());
                        $width = WordHelper::showLengthNumber($offer->getCatalogProduct()->getWidth());
                        $height = WordHelper::showLengthNumber($offer->getCatalogProduct()->getHeight());
                        if ($length > 150 || $width > 150 || $height > 150) {
                            $calculationResult->setPeriodTo(300);
                            break;
                        }
                    }
                }
                $deliveryDateOfMonth->modify(sprintf('+%s minutes', $calculationResult->getPeriodTo())); //прибавляем максимальное время доставки
                $startTime = $calculationResult->getData()['DELIVERY_START_TIME']; //когда доставка открывается
                $arStartTime = explode(':', $startTime);
                $deliveryStartTime->setTime($arStartTime[0], $arStartTime[1]); //получаем сегодня, когда доставка открывается
                $endTime = $calculationResult->getData()['DELIVERY_END_TIME']; //когда доставка закрывается
                $arEndTime = explode(':', $endTime);
                $deliveryEndTime->setTime($arEndTime[0], $arEndTime[1]); //получаем сегодня, когда доставка закроется
                $oldDayOfMonth = $calculationResult->getDeliveryDate()->format('d'); //получаем номер старого дня в месяце
                $newDayOfMonth = $deliveryDateOfMonth->format('d'); //получаем номер нового дня в месяце с учетом времени доставки
                if ($dostavistaContinue || $oldDayOfMonth != $newDayOfMonth || $deliveryDateOfMonth > $deliveryEndTime || $deliveryDate < $deliveryStartTime) {
                    continue;
                }
            }

            //тут рассчет времени доставки
            if ($calculationResult->isSuccess()) {
                $result[] = $calculationResult;
            } else {
                $errors[$calculationResult->getDeliveryCode()] = $calculationResult->getErrorMessages();
            }
        }

        if (empty($codes) && empty($result)) {
            $this->log()->info('No available deliveries', [
                'location' => $location,
                'errors' => $errors
            ]);
        }

        if ($isToEnableExtendDiscount) {
            DiscountManager::enableExtendsDiscount();
        }

        return $result;
    }

    /**
     * Получить все зоны доставки
     *
     * @param bool $withLocations
     * @return array
     */
    public function getAllZones($withLocations = true): array
    {
        if($this->allZones[intval($withLocations)] === null) {
            $this->allZones[intval($withLocations)] = $this->locationService->getLocationGroups($withLocations);
        }
        return $this->allZones[intval($withLocations)];
    }

    /**
     * Получение кода местоположения для доставки
     *
     * @param Shipment $shipment
     *
     * @return null|string
     * @throws ObjectNotFoundException
     * @throws ArgumentOutOfRangeException
     */
    public function getDeliveryLocation(Shipment $shipment): ?string
    {
        /** @noinspection PhpInternalEntityUsedInspection */
        $order = $shipment->getParentOrder();
        $propertyCollection = $order->getPropertyCollection();
        $locationProp = $propertyCollection->getDeliveryLocation();

        if ($locationProp && $locationProp->getValue()) {
            return $locationProp->getValue();
        }

        return null;
    }

    /**
     * @param bool $reload
     *
     * @return string
     */
    public function getCurrentDeliveryZone($reload = false): string
    {
        if ((null === $this->currentDeliveryZone) || $reload) {
            $this->currentDeliveryZone = $this->getDeliveryZoneByLocation(
                    $this->locationService->getCurrentLocation()
                );
        }

        return $this->currentDeliveryZone;
    }

    /**
     * Получение кода зоны доставки. Содержит либо код группы доставки,
     * либо код местоположения (в случае, если в ограничениях указано
     * отдельное местоположение)
     *
     * @param Shipment $shipment
     *
     * @return string
     * @param bool     $skipLocations
     * @throws ObjectNotFoundException
     * @throws ArgumentOutOfRangeException
     */
    public function getDeliveryZoneForShipment(Shipment $shipment, $skipLocations = true): string
    {
        if (!$deliveryLocation = $this->getDeliveryLocation($shipment)) {
            return static::ZONE_4;
        }
        $deliveryId = $shipment->getDeliveryId();

        return $this->getDeliveryZoneByDelivery($deliveryLocation, $deliveryId, $skipLocations);
    }

    /**
     * @param $deliveryLocation
     *
     * @return string
     */
    public function getDeliveryZoneByLocation($deliveryLocation): string
    {
        return $this->getDeliveryZoneCode($deliveryLocation, $this->getAllZones(true));
    }

    /**
     * @param      $deliveryLocation
     * @param      $deliveryId
     * @param bool $skipLocations
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getDeliveryZoneByDelivery($deliveryLocation, $deliveryId, $skipLocations = true): string
    {
        $availableZones = $this->getAvailableZones($deliveryId);
        return $this->getDeliveryZoneCode($deliveryLocation, $availableZones, $skipLocations);
    }

    /**
     * Получение доступных зон доставки в соответствии с ограничениями по местоположению
     *
     * @param int $deliveryId
     *
     * @return array
     */
    public function getAvailableZones(int $deliveryId): array
    {
        $allZones = $this->getAllZones();

        $getZones = function () use ($allZones, $deliveryId) {
            $result = [];

            $restrictions = DeliveryLocationTable::getList(
                [
                    'filter' => ['DELIVERY_ID' => $deliveryId],
                ]
            );

            $locationCodes = [];
            while ($restriction = $restrictions->fetch()) {
                switch ($restriction['LOCATION_TYPE']) {
                    case static::LOCATION_RESTRICTION_TYPE_LOCATION:
                        $locationCodes[] = $restriction['LOCATION_CODE'];
                        break;
                    case static::LOCATION_RESTRICTION_TYPE_GROUP:
                        if (isset($allZones[$restriction['LOCATION_CODE']])) {
                            $item = $allZones[$restriction['LOCATION_CODE']];
                            $item['TYPE'] = static::LOCATION_RESTRICTION_TYPE_GROUP;
                            $result[$restriction['LOCATION_CODE']] = $item;
                        }
                        break;
                }
            }

            if (!empty($locationCodes)) {
                $locations = LocationTable::getList(
                    [
                        'filter' => ['CODE' => $locationCodes],
                        'select' => ['ID', 'CODE', 'NAME.NAME'],
                    ]
                );

                while ($location = $locations->fetch()) {
                    // сделано, чтобы отдельные местоположения были впереди групп,
                    // т.к. группы могут их включать
                    $result = [
                            $location['CODE'] => [
                                'CODE'      => $location['CODE'],
                                'NAME'      => $location['SALE_LOCATION_LOCATION_NAME_NAME'],
                                'ID'        => $location['ID'],
                                'LOCATIONS' => [$location['CODE']],
                                'TYPE'      => static::LOCATION_RESTRICTION_TYPE_LOCATION,
                            ],
                        ] + $result;
                }
            }

            return $result;
        };

        try {
            $result = (new BitrixCache())
                ->withId(__METHOD__ . $deliveryId)
                ->withTag('location:groups')
                ->resultOf($getZones);
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get available zones: %s', $e->getMessage()), [
                'deliveryId' => $deliveryId,
            ]);
            return [];
        }

        return $result;
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isPickupCode($deliveryCode): bool
    {
        return $deliveryCode && \in_array($deliveryCode, static::PICKUP_CODES, true);
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && \in_array($deliveryCode, static::DELIVERY_CODES, true);
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDostavistaDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode === static::DELIVERY_DOSTAVISTA_CODE;
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isExpressDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode === static::EXPRESS_DELIVERY_CODE;
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDobrolapDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode === static::DOBROLAP_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDostavistaDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDostavistaDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param CalculationResultInterface $calculationResult
     * @return bool
     */
    public function isExpressDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isExpressDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDobrolapDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDobrolapDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isInnerPickupCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::INNER_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isInnerPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDpdPickupCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::DPD_PICKUP_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdPickup(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDpdPickupCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isInnerDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::INNER_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isInnerDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isInnerDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param string|null $deliveryCode
     * @return bool
     */
    public function isDpdDeliveryCode($deliveryCode): bool
    {
        return $deliveryCode && $deliveryCode === static::DPD_DELIVERY_CODE;
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDpdDelivery(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDpdDeliveryCode($calculationResult->getDeliveryCode());
    }

    /**
     * @param CalculationResultInterface $calculationResult
     *
     * @return bool
     */
    public function isDeliverable(CalculationResultInterface $calculationResult): bool
    {
        return $this->isDostavistaDelivery($calculationResult) || $this->isDelivery($calculationResult) || $this->isExpressDelivery($calculationResult);
    }

    /**
     * @param string $code
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return int
     */
    public function getDeliveryIdByCode(string $code): int
    {
        return (int)$this->getDeliveryByCode($code)['ID'];
    }

    /**
     * @param string $code
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return array
     */
    public function getDeliveryByCode(string $code): array
    {
        $delivery = DeliveryServiceTable::getList(['filter' => ['CODE' => $code]])->fetch();
        if (!$delivery) {
            throw new NotFoundException('Delivery service not found');
        }

        return $delivery;
    }

    /**
     * @param string $id
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return array
     */
    public function getDeliveryById($id): array
    {
        $delivery = DeliveryServiceTable::getList(['filter' => ['ID' => $id]])->fetch();
        if (!$delivery) {
            throw new NotFoundException('Delivery service not found');
        }

        return $delivery;
    }

    /**
     * @param int $id
     *
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @return string
     */
    public function getDeliveryCodeById(int $id): string
    {
        $delivery = DeliveryServiceTable::getList(['filter' => ['ID' => $id]])->fetch();
        if (!$delivery) {
            throw new NotFoundException('Delivery service not found');
        }

        return $delivery['CODE'];
    }

    /**
     * Получение терминалов DPD
     *
     * @param string $locationCode код местоположения
     * @param bool   $withCod      только те, где возможен наложенный платеж
     * @param float  $sum          сумма наложенного платежа
     *
     * @return StoreCollection
     */
    public function getDpdTerminalsByLocation(
        string $locationCode,
        bool $withCod = false,
        float $sum = 0
    ): StoreCollection
    {
        $result = new StoreCollection();

        $getTerminals = function () use ($locationCode) {
            /** @noinspection PhpUndefinedClassInspection */
            $terminals = TerminalTable::query()
                ->setSelect(['*'])
                ->setFilter(['LOCATION.CODE' => $locationCode])
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::class,
                        ['=this.LOCATION_ID' => 'ref.ID'],
                        ['join_type' => 'INNER']
                    )
                )
                ->exec();

            return ['result' => $terminals->fetchAll()];
        };

        try {
            /** @var array $terminals */
            $terminals = (new BitrixCache())
                ->withId(__METHOD__ . $locationCode)
                ->resultOf($getTerminals)['result'];
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get dpd terminals: %s', $e->getMessage()), [
                'location' => $locationCode,
            ]);
            return $result;
        }

        if ($withCod) {
            $terminals = array_filter(
                $terminals,
                function ($item) use ($sum) {
                    return ($item['NPP_AVAILABLE'] === 'Y') && ($item['NPP_AMOUNT'] >= $sum);
                }
            );
        }

        foreach ($terminals as $terminal) {
            $store = $this->dpdTerminalToStore($terminal, $locationCode);
            $result[$store->getXmlId()] = $store;
        }

        return $result;
    }

    /**
     * @param DeliveryResultInterface $delivery
     * @param int                     $count
     *
     * @return \DateTime[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function getNextDeliveryDates(DeliveryResultInterface $delivery, int $count = 1): array
    {
        $result = [];
        $nextDeliveries = $this->getNextDeliveries($delivery, $count);
        foreach ($nextDeliveries as $nextDelivery) {
            $result[] = $nextDelivery->getDeliveryDate();
        }

        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     * @param int                        $count
     *
     * @return DeliveryResultInterface[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     */
    public function getNextDeliveries(CalculationResultInterface $delivery, int $count = 1): array
    {
        /** @var DeliveryResultInterface[] $result */
        $result = [];
        $dateOffset = 0;

        if($this->isDelivery($delivery)){
            /** @var \DateTime $lastDate */
            $lastDate = null;

            do {
                /** @var DeliveryResultInterface $currentDelivery */
                $currentDelivery = clone $delivery;
                $currentDeliveryDate = $currentDelivery->setDateOffset($dateOffset)->getDeliveryDate();
                if ((null === $lastDate) ||
                    ($lastDate->getTimestamp() < $currentDeliveryDate->getTimestamp())
                ) {
                    $result[] = $currentDelivery;
                }

                $lastDate = $currentDeliveryDate;
                $dateOffset++;
            } while (\count($result) < $count);
        } else {
            while(\count($result) < $count){
                $deliveryDate = clone $delivery->getDeliveryDate();
                $deliveryDate->modify(sprintf('+%s days', $dateOffset));

                /** @var PickupResult $tmpPickup */
                $tmpPickup = clone $delivery;
                $tmpPickup->setDeliveryDate($deliveryDate);
                $result[] = $tmpPickup;
                $dateOffset++;
            }
        }

        return $result;
    }

    /**
     * @param $code
     *
     * @return Terminal|null
     */
    public function getDpdTerminalByCode($code): ?Terminal
    {
        $getTerminal = function () use ($code) {
            /** @noinspection PhpUndefinedClassInspection */
            $terminal = TerminalTable::query()->setSelect(['*', 'LOCATION.CODE'])
                ->setFilter(['CODE' => $code])
                ->registerRuntimeField(
                    new ReferenceField(
                        'LOCATION',
                        LocationTable::class,
                        ['=this.LOCATION_ID' => 'ref.ID'],
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()->fetch();
            if (!$terminal) {
                throw new TerminalNotFoundException(sprintf('Terminal with code %s not found', $code));
            }

            return ['result' => $terminal];
        };

        $result = null;
        try {
            $terminal = (new BitrixCache())
                ->withId(__METHOD__ . $code)
                ->resultOf($getTerminal)['result'];

            $result = $this->dpdTerminalToStore(
                $terminal,
                $terminal['FOURPAWS_DELIVERYBUNDLE_DPD_TERMINAL_LOCATION_CODE']
            );
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to get dpd terminal: %s', $e->getMessage()), [
                'code' => $code,
            ]);
        }

        return $result;
    }/** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param Offer $offer
     * @param CalculationResultInterface $delivery
     * @param int $quantity
     * @param float $price
     *
     * @return StockResultCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws StoreNotFoundException
     */
    public function getStockResultForOffer(
        Offer $offer,
        CalculationResultInterface $delivery,
        int $quantity = null,
        float $price = null
    ): StockResultCollection
    {
        $priceForAmountCollection = new PriceForAmountCollection();
        $priceForAmountCollection->add((new PriceForAmount())
            ->setAmount($quantity ?? 1)
//            ->setPrice($price ?? $offer->getPrice())
            ->setPrice($offer->getPrice())
        );

        return DeliveryHandlerBase::getStocksForItem(
            $offer,
            $priceForAmountCollection,
            $this->getStoresByDelivery($delivery)
        );
    }

    /**
     * @param CalculationResultInterface $delivery
     *
     * @return StoreCollection
     * @throws ApplicationCreateException
     * @throws ArgumentException
     */
    public function getStoresByDelivery(CalculationResultInterface $delivery): StoreCollection
    {
        return DeliveryHandlerBase::getAvailableStores($delivery->getDeliveryCode(), $delivery->getDeliveryZone());
    }

    /**
     * @param Basket $basket
     * @return PriceForAmountCollection[]
     */
    public function getBasketPrices(Basket $basket) {
        return DeliveryHandlerBase::getBasketPrices($basket);
    }

    /**
     * @param string          $locationCode
     * @param BasketBase|null $basket
     *
     * @return Shipment
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws ArgumentTypeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserMessageException
     * @throws \Exception
     */
    protected function generateShipment(string $locationCode, BasketBase $basket = null): Shipment
    {
        $order = Order::create(
            SITE_ID,
            null,
            CurrencyManager::getBaseCurrency()
        );

        /** @noinspection PhpInternalEntityUsedInspection */
        $order->setMathActionOnly(true);

        if (!$basket) {
            $basket = Basket::createFromRequest([]);
        }

        $order->setBasket($basket);

        $propertyCollection = $order->getPropertyCollection();
        $locationProp = $propertyCollection->getDeliveryLocation();
        $locationProp->setValue($locationCode);

        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        try {
            $shipment->setField('CURRENCY', $order->getCurrency());
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Failed to set shipment currency: %s', $e->getMessage()), [
                'location' => $locationCode,
            ]);
        }

        try {
            /** @var BasketItem $item */
            foreach ($order->getBasket() as $item) {
                $shipmentItem = $shipmentItemCollection->createItem($item);
                $shipmentItem->setFieldNoDemand('QUANTITY', $item->getQuantity());
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('Failed to set shipmentItem quantity: %s', $e->getMessage()), [
                'location' => $locationCode,
            ]);
        }

        return $shipment;
    }

    /**
     * @param array  $terminal
     * @param string $locationCode
     *
     * @return Terminal
     */
    protected function dpdTerminalToStore(array $terminal, string $locationCode = ''): Terminal
    {
        $schedule = str_replace('<br>', '. ', $terminal['SCHEDULE_SELF_DELIVERY']);

        $store = new Terminal();
        $nppAvailable = $terminal['NPP_AVAILABLE'] === BitrixUtils::BX_BOOL_TRUE;
        $store->setNppAvailable($nppAvailable)
            ->setNppValue((int)$terminal['NPP_AMOUNT'])
            ->setCardPayment($nppAvailable ?(bool)$terminal['SCHEDULE_PAYMENT_CASHLESS'] : false)
            ->setCashPayment($nppAvailable ? (bool)$terminal['SCHEDULE_PAYMENT_CASH'] : false)
            ->setTitle((string)$terminal['NAME'])
            ->setLocation($locationCode)
            ->setAddress((string)$terminal['ADDRESS_SHORT'])
            ->setCode((string)$terminal['CODE'])
            ->setXmlId((string)$terminal['CODE'])
            ->setLatitude((float)$terminal['LATITUDE'])
            ->setLongitude((float)$terminal['LONGITUDE'])
            ->setLocationId((int)$terminal['LOCATION_ID'])
            ->setScheduleString($schedule)
            ->setDescription((string)$terminal['ADDRESS_DESCR']);

        return $store;
    }

    /**
     * @param string $locationCode
     * @param array  $zones
     * @param bool   $skipLocations
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getDeliveryZoneCode(string $locationCode, array $zones = [], $skipLocations = true): string
    {
        if (($location = $this->locationService->findLocationByCode($locationCode)) && $location['PATH']) {
            foreach ($location['PATH'] as $pathItem) {
                $deliveryLocationPath[] = $pathItem['CODE'];
            }
            $deliveryLocationPath[] = $location['CODE'];
        } else {
            $deliveryLocationPath[] = $locationCode;
        }

        $result = [];
        foreach ($zones as $code => $zone) {
            if ($skipLocations && $zone['TYPE'] === static::LOCATION_RESTRICTION_TYPE_LOCATION) {
                continue;
            }

            $found = array_intersect($deliveryLocationPath, $zone['LOCATIONS']);
            foreach ($found as $item) {
                $result[\array_search($item, $deliveryLocationPath, true)] = $zone['CODE'];
            }
        }

        return empty($result) ? static::ZONE_4 : $result[\max(\array_keys($result))];
    }

    static function getZonesTwo():array
    {
        return [
            self::ZONE_2,
            self::ZONE_NIZHNY_NOVGOROD,
            self::ZONE_NIZHNY_NOVGOROD_REGION,
            self::ZONE_VLADIMIR,
            self::ZONE_VLADIMIR_REGION,
            self::ZONE_VORONEZH,
            self::ZONE_VORONEZH_REGION,
            self::ZONE_YAROSLAVL,
            self::ZONE_YAROSLAVL_REGION,
            self::ZONE_TULA,
            self::ZONE_TULA_REGION,
            self::ZONE_KALUGA,
            self::ZONE_KALUGA_REGION,
            self::ZONE_IVANOVO,
            self::ZONE_IVANOVO_REGION
        ];
    }

    /**
     * @param $locationCode
     * @param ExpressDeliveryResult $selectedDelivery
     * @return int
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws LocationNotFoundException
     */
    public function getExpressDeliveryInterval($locationCode, ExpressDeliveryResult $selectedDelivery = null): int
    {
        $locationGroups = $this->locationService->findLocationGroupsByCode($locationCode);

        if (empty($locationGroups)) {
            throw new LocationNotFoundException('Не найдены группы местоположения');
        }

        $deliveryInterval = 0;

        foreach ($locationGroups as $group) {
            switch ($group) {
                case self::ZONE_EXPRESS_DELIVERY_45:
                    $deliveryInterval = ($selectedDelivery !== null) ? (int)$selectedDelivery->getData()['PERIOD_FROM'] : 45;
                    break;
                case self::ZONE_EXPRESS_DELIVERY_90:
                    $deliveryInterval = ($selectedDelivery !== null) ? (int)$selectedDelivery->getData()['PERIOD_TO'] : 90;
                    break;
                default:
                    throw new LocationNotFoundException('Неверный район для экспресс доставки');
            }
        }

        return $deliveryInterval;
    }
}
