<?

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\Helpers\BxCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use Bitrix\Currency;

/**
 * Class DobrolapDeliveryHandler
 *
 * @package FourPaws\DeliveryBundle\Handler
 * @bxnolanginspection
 */
class DobrolapDeliveryHandler extends DeliveryHandlerBase
{
    protected const ORDER_DELIVERY_PLACE_CODE_PROP = 'DELIVERY_PLACE_CODE';

    protected $code = 'dobrolap_delivery';

    /**
     * DobrolapDeliveryHandler constructor.
     *
     * @param array $initParams
     *
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle(): string
    {
        return 'Доставка в приют';
    }

    public static function getClassDescription(): string
    {
        return 'Доставка в приют';
    }

    /**
     * @param Shipment $shipment
     *
     * @return bool
     * @throws ObjectNotFoundException
     */
    public function isCompatible(Shipment $shipment): bool
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        return true;
    }

    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        return new IntervalCollection();
    }

    /**
     * @param Shipment $shipment
     *
     * @throws ApplicationCreateException
     * @throws SystemException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws NotFoundException
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();

        if (!$deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment)) {
            $result->addError(new Error('Не задано местоположение доставки'));

            return $result;
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        /** @var Basket $basket */
        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $deliveryZone = $this->deliveryService->getDeliveryZoneForShipment($shipment, false);
        $data = [];

        $propertyCollection = $shipment->getParentOrder()->getPropertyCollection();
        $deliveryCost = BxCollection::getOrderPropertyByCode($propertyCollection, 'DELIVERY_COST');

        if ($deliveryCost && null !== ($deliveryCostValue = $deliveryCost->getValue()))
        {
            /**
             * Хак для сохранения кастомной цены доставки, исправляющий баг при добавлении в заказ товаров
             * через метод \FourPaws\SaleBundle\Service\BasketService::addOfferToBasket
             * в обработчиках события OnSaleOrderBeforeSaved
             */

            $result->setDeliveryPrice($deliveryCostValue);
        }
        else
        {
            $result->setDeliveryPrice(0);
        }

//        $data['INTERVALS'] = $this->getIntervals($shipment);
//        $data['WEEK_DAYS'] = $this->getWeekDays($shipment);
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

        $stockResult = static::getStocks($basket, $offers, $availableStores);

        $data['STOCK_RESULT'] = $stockResult;

        $result->setData($data);
        if ($stockResult->getOrderable()->isEmpty()) {
            $result->addError(new Error('Отсутствуют товары в наличии'));
        }

        return $result;
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    protected function getConfigStructure() :array
    {
        $currency = $this->currency;

        if(Loader::includeModule('currency'))
        {
            $currencyList = Currency\CurrencyManager::getCurrencyList();
            if (isset($currencyList[$this->currency]))
                $currency = $currencyList[$this->currency];
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
        if((!isset($fields["CODE"]) || intval($fields["CODE"]) < 0) && isset($fields["ID"]) && intval($fields["ID"]) > 0)
            $fields["CODE"] = $fields["ID"];

        return parent::prepareFieldsForSaving($fields);
    }
}
