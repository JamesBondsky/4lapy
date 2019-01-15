<?php

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Currency;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Shipment;
use COption;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use Bitrix\Main\Error;
use Bitrix\Sale\Basket;

Loc::loadMessages(__FILE__);

/**
 * Class DostavistaDeliveryHandler
 * Simple class for delivery service.
 * Old configurable type converted to this type.
 * @package Bitrix\Sale\Delivery\Services
 */
class DostavistaDeliveryHandler extends DeliveryHandlerBase
{
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = true;

    /**
     * @param array $initParams Initial data params from table record.
     * @throws SystemException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);

        if (!isset($this->config["MAIN"]["PRICE"])) {
            $this->config["MAIN"]["PRICE"] = "0";
        }

        if (!isset($initParams["CURRENCY"])) {
            $initParams["CURRENCY"] = "RUB";
        }

        if (!isset($this->config["MAIN"]["PERIOD"]) || !is_array($this->config["MAIN"]["PERIOD"])) {
            $this->config["MAIN"]["PERIOD"] = array();
            $this->config["MAIN"]["PERIOD"]["FROM"] = "0";
            $this->config["MAIN"]["PERIOD"]["TO"] = "0";
            $this->config["MAIN"]["PERIOD"]["TYPE"] = "D";
        }


    }

    /**
     * @param Shipment $shipment
     * @return IntervalCollection
     */
    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        $result = new IntervalCollection();
        return $result;
    }

    /**
     * @return string
     */
    public static function getClassTitle(): string
    {
        return 'Доставка "Достависта"';
    }

    /**
     * @return string
     */
    public static function getClassDescription(): string
    {
        return 'Обработчик собственной доставки "Достависта"';
    }

    /**
     * @return string Period text.
     */
    protected function getPeriodText()
    {
        $result = "";

        if (IntVal($this->config["MAIN"]["PERIOD"]["FROM"]) > 0 || IntVal($this->config["MAIN"]["PERIOD"]["TO"]) > 0) {
            $result = "";

            if (IntVal($this->config["MAIN"]["PERIOD"]["FROM"]) > 0) {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_FROM") . " " . IntVal($this->config["MAIN"]["PERIOD"]["FROM"]);
            }

            if (IntVal($this->config["MAIN"]["PERIOD"]["TO"]) > 0) {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_TO") . " " . IntVal($this->config["MAIN"]["PERIOD"]["TO"]);
            }

            if ($this->config["MAIN"]["PERIOD"]["TYPE"] == "MIN") {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_MIN") . " ";
            } elseif ($this->config["MAIN"]["PERIOD"]["TYPE"] == "H") {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_HOUR") . " ";
            } elseif ($this->config["MAIN"]["PERIOD"]["TYPE"] == "M") {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_MONTH") . " ";
            } else {
                $result .= " " . Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_DAY") . " ";
            }
        }

        return $result;
    }

    protected function calculateConcrete(Shipment $shipment = null)
    {
        $result = new CalculationResult();
        $price = $this->config["MAIN"]["PRICE"];

        if ($shipment && Loader::includeModule('currency')) {
            $rates = new \CCurrencyRates;
            $currency = $this->currency;
            $shipmentCurrency = $shipment->getCollection()->getOrder()->getCurrency();
            $price = $rates->convertCurrency($price, $currency, $shipmentCurrency);
        }

        $result->setDeliveryPrice(
            roundEx(
                $price,
                SALE_VALUE_PRECISION
            )
        );

        $result->setPeriodDescription($this->getPeriodText());
        $result->setPeriodFrom($this->config["MAIN"]["PERIOD"]["FROM"]);
        $result->setPeriodTo($this->config["MAIN"]["PERIOD"]["TO"]);
        $result->setPeriodType($this->config["MAIN"]["PERIOD"]["TYPE"]);

        if (!$deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment)) {
            $result->addError(new Error('Не задано местоположение доставки'));

            return $result;
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        /** @var Basket $basket */
        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $data = [];
        if ($this->config['MAIN']['PRICE']) {
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
        $stockResult = static::getStocksForAllAvailableOffers($basket, $offers, $availableStores);

        if ($stockResult->getOrderable()->isEmpty()) {
            $result->addError(new Error('Отсутствуют товары в наличии'));
        }

        $result->setData(
            [
                'STOCK_RESULT' => $stockResult,
                'FREE_PRICE_FROM' => (int)$this->config["MAIN"]["FREE_PRICE_FROM"],
                'DEV_MODE' => COption::GetOptionString('articul.dostavista.delivery', 'dev_mode', 'N'),
                'TOKEN_PROD' => COption::GetOptionString('articul.dostavista.delivery', 'token_prod', ''),
                'CLIENT_ID_DEV' => COption::GetOptionString('articul.dostavista.delivery', 'client_id_dev', ''),
                'TOKEN_DEV' => COption::GetOptionString('articul.dostavista.delivery', 'token_dev', ''),
                'DELIVERY_START_TIME' => COption::GetOptionString('articul.dostavista.delivery', 'delivery_start_time', '00:00'),
                'DELIVERY_END_TIME' => COption::GetOptionString('articul.dostavista.delivery', 'delivery_end_time', '23:59'),
                'TEXT_EXPRESS_DELIVERY' => COption::GetOptionString('articul.dostavista.delivery', 'text_express_delivery', 'Текст с информацией, что пользователю доступна Экспресс-доставка'),
                'TEXT_EXPRESS_DELIVERY_TIME' => COption::GetOptionString('articul.dostavista.delivery', 'text_express_delivery_time', 'Текст с временем доставки для кнопки Экспресс-доставки')
            ]
        );

        return $result;
    }

    /**
     * @return array
     * @throws \Exception
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

        return array(

            "MAIN" => array(
                "TITLE" => Loc::getMessage("SALE_DLVR_HANDL_CONF_TITLE"),
                "DESCRIPTION" => Loc::getMessage("SALE_DLVR_HANDL_CONF_DESCRIPTION"),
                "ITEMS" => array(

                    "CURRENCY" => array(
                        "TYPE" => "DELIVERY_READ_ONLY",
                        "NAME" => Loc::getMessage("SALE_DLVR_HANDL_CONF_CURRENCY"),
                        "VALUE" => $this->currency,
                        "VALUE_VIEW" => htmlspecialcharsbx($currency)
                    ),

                    "PRICE" => array(
                        "TYPE" => "NUMBER",
                        "MIN" => 0,
                        "NAME" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PRICE")
                    ),

                    "FREE_PRICE_FROM" => array(
                        "TYPE" => "NUMBER",
                        "MIN" => 0,
                        "NAME" => 'Бесплатная доставка от'
                    ),

                    "PERIOD" => array(
                        "TYPE" => "DELIVERY_PERIOD",
                        "NAME" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_DLV"),
                        "ITEMS" => array(
                            "FROM" => array(
                                "TYPE" => "NUMBER",
                                "MIN" => 0,
                                "NAME" => "" //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_FROM"),
                            ),
                            "TO" => array(
                                "TYPE" => "NUMBER",
                                "MIN" => 0,
                                "NAME" => "&nbsp;-&nbsp;" //Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_TO"),
                            ),
                            "TYPE" => array(
                                "TYPE" => "ENUM",
                                "OPTIONS" => array(
                                    "MIN" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_MIN"),
                                    "H" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_HOUR"),
                                    "D" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_DAY"),
                                    "M" => Loc::getMessage("SALE_DLVR_HANDL_CONF_PERIOD_MONTH")
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    public static function getAdminFieldsList()
    {
        $result = parent::getAdminFieldsList();
        $result["STORES"] = true;
        return $result;
    }

    public function prepareFieldsForSaving(array $fields)
    {
        if ((!isset($fields["CODE"]) || intval($fields["CODE"]) < 0) && isset($fields["ID"]) && intval($fields["ID"]) > 0) {
            $fields["CODE"] = $fields["ID"];
        }

        return parent::prepareFieldsForSaving($fields);
    }

    public static function onAfterAdd($serviceId, array $fields = array())
    {
        if ($serviceId <= 0) {
            return false;
        }

        $res = Manager::update($serviceId, array('CODE' => $serviceId));
        return $res->isSuccess();
    }

    public function isCalculatePriceImmediately(): bool
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }
}