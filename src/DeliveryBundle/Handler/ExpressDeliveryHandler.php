<?php

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Currency;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use CCurrencyRates;
use COption;
use DateTime;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use Bitrix\Main\Error;
use Bitrix\Sale\Basket;
use FourPaws\StoreBundle\Exception\NotFoundException;

Loc::loadMessages(__FILE__);

/**
 * @package Bitrix\Sale\Delivery\Services
 */
class ExpressDeliveryHandler extends DeliveryHandlerBase
{
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = true;

    /**
     * @param array $initParams Initial data params from table record.
     * @throws SystemException
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);

        if (!isset($this->config['MAIN']['PRICE'])) {
            $this->config['MAIN']['PRICE'] = '0';
        }

        if (!isset($initParams['CURRENCY'])) {
            $initParams['CURRENCY'] = 'RUB';
        }

        if (!isset($this->config['MAIN']['PERIOD']) || !is_array($this->config['MAIN']['PERIOD'])) {
            $this->config['MAIN']['PERIOD'] = [];
            $this->config['MAIN']['PERIOD']['FROM'] = '0';
            $this->config['MAIN']['PERIOD']['TO'] = '0';
            $this->config['MAIN']['PERIOD']['TYPE'] = 'D';
        }
    }

    /**
     * @param Shipment $shipment
     * @return IntervalCollection
     */
    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        return new IntervalCollection();
    }

    /**
     * @return string
     */
    public static function getClassTitle(): string
    {
        return 'Экспресс-доставка "4 лапы"';
    }

    /**
     * @return string
     */
    public static function getClassDescription(): string
    {
        return 'Обработчик собственной экспресс-доставки "4 лапы"';
    }

    /**
     * @return string Period text.
     */
    protected function getPeriodText(): string
    {
        $result = '';

        $periodFrom = IntVal($this->config['MAIN']['PERIOD']['FROM']);
        $periodTo = IntVal($this->config['MAIN']['PERIOD']['TO']);

        if ($periodFrom > 0 || $periodTo > 0) {
            $result = '';

            if ($periodFrom) {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_FROM') . ' ' . IntVal($this->config['MAIN']['PERIOD']['FROM']);
            }

            if ($periodTo) {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_TO') . ' ' . IntVal($this->config['MAIN']['PERIOD']['TO']);
            }

            if ($this->config['MAIN']['PERIOD']['TYPE'] === 'MIN') {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MIN') . ' ';
            } elseif ($this->config['MAIN']['PERIOD']['TYPE'] === 'H') {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_HOUR') . ' ';
            } elseif ($this->config['MAIN']['PERIOD']['TYPE'] === 'M') {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MONTH') . ' ';
            } else {
                $result .= ' ' . Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_DAY') . ' ';
            }
        }

        return $result;
    }

    /**
     * @param Shipment|null $shipment
     * @return CalculationResult
     * @throws ArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws LoaderException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    protected function calculateConcrete(Shipment $shipment = null): CalculationResult
    {
        $result = new CalculationResult();

        $currentHour = (int)(new DateTime())->format('h');

        if (($currentHour < 10) || ($currentHour > 19)) {
            $result->addError(new Error('В данное время экспресс доставка не работает'));
        }

        $price = $this->config['MAIN']['PRICE'];

        if ($shipment && Loader::includeModule('currency')) {
            $currency = $this->currency;
            /** @var Order $order */
            $order = $shipment->getCollection()->getOrder();
            $shipmentCurrency = $order->getCurrency();
            $price = CCurrencyRates::convertCurrency($price, $currency, $shipmentCurrency);
        }

        $result->setDeliveryPrice(
            roundEx(
                $price,
                SALE_VALUE_PRECISION
            )
        );

        $result->setPeriodDescription($this->getPeriodText());
        $result->setPeriodFrom($this->config['MAIN']['PERIOD']['FROM']);
        $result->setPeriodTo($this->config['MAIN']['PERIOD']['TO']);
        $result->setPeriodType($this->config['MAIN']['PERIOD']['TYPE']);

        if (!$deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment)) {
            $result->addError(new Error('Не задано местоположение доставки'));

            return $result;
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        /** @var Basket $basket */
        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $data = [];

        if (isset($this->config['MAIN']['PRICE'])) {
            $result->setDeliveryPrice($this->config['MAIN']['PRICE']);

            if (!empty($this->config['MAIN']['FREE_PRICE_FROM'])) {
                $data['FREE_FROM'] = (int)$this->config['MAIN']['FREE_PRICE_FROM'];
            }
        } else {
            $result->addError(new Error('Не задана стоимость доставки'));
        }
        $deliveryZone = $this->deliveryService->getDeliveryZoneForShipment($shipment, true);
        if (!$offers = static::getOffers($basket)) {
            $result->setData($data);

            /**
             * Нужно для отображения списка доставок в хедере и на странице доставок
             */
            return $result;
        }

        $availableStores = self::getAvailableStores($this->code, $deliveryZone, $deliveryLocation);

        if ($availableStores->isEmpty()) {
            $result->addError(new Error('Не найдено доступных складов'));

            return $result;
        }

        //проверка остатков всех офферов, чтобы они были в хотя бы в одном магазине
        $stockResult = $this->getStocksForAllAvailableOffers($basket, $offers, $availableStores, false);

        if ($stockResult->getOrderable()->isEmpty()) {
            $result->addError(new Error('Отсутствуют товары в наличии'));
        }

        $result->setData(
            [
                'STOCK_RESULT' => $stockResult,
                'PERIOD_FROM' => $this->config['MAIN']['PERIOD']['FROM'],
                'PERIOD_TO' => $this->config['MAIN']['PERIOD']['TO'],
            ]
        );

        return $result;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getConfigStructure(): array
    {
        $currency = $this->currency;

        if (Loader::includeModule('currency')) {
            $currencyList = Currency\CurrencyManager::getCurrencyList();
            if (isset($currencyList[$this->currency])) {
                $currency = $currencyList[$this->currency];
            }
            unset($currencyList);
        }

        return [
            'MAIN' => [
                'TITLE' => Loc::getMessage('SALE_DLVR_HANDL_CONF_TITLE'),
                'DESCRIPTION' => Loc::getMessage('SALE_DLVR_HANDL_CONF_DESCRIPTION'),
                'ITEMS' => [

                    'CURRENCY' => [
                        'TYPE' => 'DELIVERY_READ_ONLY',
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_CONF_CURRENCY'),
                        'VALUE' => $this->currency,
                        'VALUE_VIEW' => htmlspecialcharsbx($currency)
                    ],

                    'PRICE' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PRICE')
                    ],

                    'FREE_PRICE_FROM' => [
                        'TYPE' => 'NUMBER',
                        'MIN' => 0,
                        'NAME' => 'Бесплатная доставка от'
                    ],

                    'PERIOD' => [
                        'TYPE' => 'DELIVERY_PERIOD',
                        'NAME' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_DLV'),
                        'ITEMS' => [
                            'FROM' => [
                                'TYPE' => 'NUMBER',
                                'MIN' => 0,
                                'NAME' => '' //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_FROM"),
                            ],
                            'TO' => [
                                'TYPE' => 'NUMBER',
                                'MIN' => 0,
                                'NAME' => '&nbsp;-&nbsp;' //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_TO"),
                            ],
                            'TYPE' => [
                                'TYPE' => 'ENUM',
                                'OPTIONS' => [
                                    'MIN' => Loc::getMessage('SALE_DLVR_HANDL_CONF_PERIOD_MIN')
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getAdminFieldsList(): array
    {
        $result = parent::getAdminFieldsList();
        $result['STORES'] = true;
        return $result;
    }

    /**
     * @param array $fields
     * @return array
     * @throws SystemException
     */
    public function prepareFieldsForSaving(array $fields): array
    {
        if ((!isset($fields['CODE']) || intval($fields['CODE']) < 0) && isset($fields['ID']) && intval($fields['ID']) > 0) {
            $fields['CODE'] = $fields['ID'];
        }

        return parent::prepareFieldsForSaving($fields);
    }

    /**
     * @param int $serviceId
     * @param array $fields
     * @return bool
     *
     * @throws SystemException
     */
    public static function onAfterAdd($serviceId, array $fields = []): bool
    {
        if ($serviceId <= 0) {
            return false;
        }

        $res = Manager::update($serviceId, ['CODE' => $serviceId]);
        return $res->isSuccess();
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately(): bool
    {
        return self::$isCalculatePriceImmediately;
    }

    /**
     * @return bool
     */
    public static function whetherAdminExtraServicesShow(): bool
    {
        return self::$whetherAdminExtraServicesShow;
    }
}
