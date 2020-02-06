<?php
namespace Ipolh\DPD\DB\Order;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Bitrix\Main\SystemException;

use \Ipolh\DPD\Utils;
use \Ipolh\DPD\Order as DpdOrder;
use \Ipolh\DPD\DB\AbstractModel;
use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\TerminalsManager;
use \Ipolh\DPD\Delivery\DPD;

Loc::loadMessages(__FILE__);

class Model extends AbstractModel
{
	/**
	 * Поля битриксового заказа
	 * @var array
	 */
	protected $orderFields;

	/**
	 * Св-ва битриксового заказа
	 * @var array
	 */
	protected $orderProps;

	/**
	 * Состав битриксового заказа
	 * @var array
	 */
	protected $orderItems;

	/**
	 * Отправление
	 * @var \Ipolh\DPD\Shipment
	 */
	protected $shipment;

	/**
	 * @return string
	 */
	public static function DataManager()
	{
		return '\\Ipolh\\DPD\\DB\\Order\\Table';
	}

	/**
	 * Возвращает список статусов и их описаний
	 */
	public static function StatusList()
	{
		return array(
			DpdOrder::STATUS_NEW              => Loc::getMessage('DPD_ORDER_STATUS_NEW'),
			DpdOrder::STATUS_OK               => Loc::getMessage('DPD_ORDER_STATUS_OK'),
			DpdOrder::STATUS_PENDING          => Loc::getMessage('DPD_ORDER_STATUS_PENDING'),
			DpdOrder::STATUS_ERROR            => Loc::getMessage('DPD_ORDER_STATUS_ERROR'),
			DpdOrder::STATUS_CANCEL           => Loc::getMessage('DPD_ORDER_STATUS_CANCEL'),
			DpdOrder::STATUS_CANCEL_PREV      => Loc::getMessage('DPD_ORDER_STATUS_CANCEL_PREV'),
			DpdOrder::STATUS_NOT_DONE         => Loc::getMessage('DPD_ORDER_STATUS_NOT_DONE'),
			DpdOrder::STATUS_DEPARTURE        => Loc::getMessage('DPD_ORDER_STATUS_DEPARTURE'),
			DpdOrder::STATUS_TRANSIT          => Loc::getMessage('DPD_ORDER_STATUS_TRANSIT'),
			DpdOrder::STATUS_TRANSIT_TERMINAL => Loc::getMessage('DPD_ORDER_STATUS_TRANSIT_TERMINAL'),
			DpdOrder::STATUS_ARRIVE           => Loc::getMessage('DPD_ORDER_STATUS_ARRIVE'),
			DpdOrder::STATUS_COURIER          => Loc::getMessage('DPD_ORDER_STATUS_COURIER'),
			DpdOrder::STATUS_DELIVERED        => Loc::getMessage('DPD_ORDER_STATUS_DELIVERED'),
			DpdOrder::STATUS_LOST             => Loc::getMessage('DPD_ORDER_STATUS_LOST'),
			DpdOrder::STATUS_PROBLEM          => Loc::getMessage('DPD_ORDER_STATUS_PROBLEM'),
			DpdOrder::STATUS_RETURNED         => Loc::getMessage('DPD_ORDER_STATUS_RETURNED'),
			DpdOrder::STATUS_NEW_DPD          => Loc::getMessage('DPD_ORDER_STATUS_NEW_DPD'),
			DpdOrder::STATUS_NEW_CLIENT       => Loc::getMessage('DPD_ORDER_STATUS_NEW_CLIENT'),
		);
	}

	/**
	 * Получаем информацию по заказу
	 * ассоциированному с записью
	 *
	 * @return void
	 */
	public function afterLoad()
	{
		$this->loadOrder();
	}

	/**
	 * Заполняет поля на основе данных заказа
	 *
	 * @param mixed $orderId ID или массив заказа
	 * @return void
	 */
	public function fillFromOrder($orderId)
	{
		$this->orderId          = $orderId;
		$this->orderDate        = $this->orderFields['DATE_INSERT'];
		$this->price            = $this->orderFields['PRICE'] - $this->orderFields['PRICE_DELIVERY'];
		$this->priceDelivery    = $this->orderFields['PRICE_DELIVERY'];
		$this->receiverLocation = $this->orderProps['IPOLH_DPD_LOCATION'];
		$this->receiverComment  = $this->orderFields['USER_DESCRIPTION'];

		$address_fields = array('FIO', 'NAME', 'PHONE', 'LOCATION', 'STREET',  'STREETABBR', 'HOUSE', 'KORPUS', 'STR', 'VLAD', 'OFFICE', 'FLAT');
		foreach ($address_fields as $field) {
			$fname = 'RECEIVER_'. $field;
			$code  = Option::get(IPOLH_DPD_MODULE, $fname .'_'. $this->orderFields['PERSON_TYPE_ID']);
			$this->$fname = $this->orderProps[$code];
		}

		$shipment = $this->getShipment(true);

		$deliveryCode = DPD::getDeliveryCode($this->orderFields['DELIVERY_ID']);
		$profile      = DPD::getDeliveryProfile($deliveryCode);
		if ($this->getServiceVariant() != $this->setServiceVariant($profile)->getServiceVariant()) {		
			$shipment          = $this->getShipment(true);
			$tariff            = $shipment->calculator()->calculate();
			$this->serviceCode = $tariff['SERVICE_CODE'];
		}

		$this->cargoValue      = $shipment->getDeclaredValue() ? $this->price : 0;
		$this->npp             = $shipment->isPaymentOnDelivery($this->receiverTerminalCode) && $this->orderFields['PAYED'] != 'Y' ? 'Y' : 'N';
		$this->sumNpp          = $this->npp == 'Y' ? $this->orderFields['PRICE'] - $this->orderFields['SUM_PAID'] : 0;
		$this->dimensionWidth  = $shipment->getWidth();
		$this->dimensionHeight = $shipment->getHeight();
		$this->dimensionLength = $shipment->getLength();
		$this->cargoVolume     = $shipment->getVolume();
		$this->cargoWeight     = $shipment->getWeight();
	}

	/**
	 * Заполняет поля на основе настроек модуля
	 *
	 * @return void
	 */
	public function fillFromConfig()
	{
		$this->SENDER_LOCATION = Utils::getSaleLocationId();

		$fields = array('PICKUP_TIME_PERIOD', 'DELIVERY_TIME_PERIOD', 'CARGO_CATEGORY', 'CARGO_NUM_PACK',
			'SENDER_FIO', 'SENDER_NAME', 'SENDER_PHONE', 'SENDER_STREET',  'SENDER_STREETABBR', 'SENDER_HOUSE',
			'SENDER_KORPUS', 'SENDER_STR', 'SENDER_VLAD', 'SENDER_OFFICE', 'SENDER_FLAT', 'CARGO_REGISTERED',
			'DVD', 'TRM', 'PRD', 'VDO', 'OGD', 'SMS', 'EML', 'ESD', 'ESZ', 'POD', 'SENDER_TERMINAL_CODE', 'PAYMENT_TYPE',
		);

		foreach ($fields as $field) {
			$this->$field = Option::get(IPOLH_DPD_MODULE, $field, '');
		}
	}

	/**
	 * Возвращает отправку
	 *
	 * @return \Ipolh\DPD\Shipment
	 */
	public function getShipment($forced = false)
	{
		if (is_null($this->shipment) || $forced) {
			$this->shipment = new \Ipolh\DPD\Shipment();
			$this->shipment->setSender($this->senderLocation);
			$this->shipment->setReceiver($this->receiverLocation);
			$this->shipment->setPaymentMethod($this->orderFields['PERSON_TYPE_ID'], $this->orderFields['PAY_SYSTEM_ID']);
			$this->shipment->setItems($this->orderItems, $this->sumNpp);

			list($selfPickup, $selfDelivery) = array_values($this->getServiceVariant());
			$this->shipment->setSelfPickup($selfPickup);
			$this->shipment->setSelfDelivery($selfDelivery);

			if ($this->isCreated()) {
				$this->shipment->setWidth($this->dimensionWidth);
				$this->shipment->setHeight($this->dimensionHeight);
				$this->shipment->setLength($this->dimensionLength);
				$this->shipment->setWeight($this->cargoWeight);
			}
		}

		return $this->shipment;
	}

	/**
	 * Устанавливает ID заказа битрикса
	 *
	 * @param int $orderId
	 */
	public function setOrderId($orderId)
	{
		$this->loadOrder($orderId);
		$this->fields['ORDER_ID'] = $this->orderFields['ID'];
	}

	/**
	 * Устанавливает вариант доставки
	 *
	 * @param string $variant
	 */
	public function setServiceVariant($variant)
	{
		$D = Loc::getMessage('DPD_ORDER_SERVICE_VARIANT_D');
		$T = Loc::getMessage('DPD_ORDER_SERVICE_VARIANT_T');

		if (is_string($variant) && preg_match('~^('. $D .'|'. $T .'){2}$~sUi', $variant)) {
			$this->fields['SERVICE_VARIANT'] = $variant;
			return;
		}

		if (is_array($variant)) {
			$selfPickup   = $variant['SELF_PICKUP'];
			$selfDelivery = $variant['SELF_DELIVERY'];
		} else {
			$selfPickup   = Option::get(IPOLH_DPD_MODULE, 'SELF_PICKUP');
			$selfDelivery = $variant == 'PICKUP';
		}

		$this->fields['SERVICE_VARIANT'] = ''
			. ($selfPickup   ? $T : $D)
			. ($selfDelivery ? $T : $D)
		;

		return $this;
	}

	/**
	 * Возвращает вариант доставки
	 *
	 * @return array
	 */
	public function getServiceVariant()
	{
		$D = Loc::getMessage('DPD_ORDER_SERVICE_VARIANT_D');
		$T = Loc::getMessage('DPD_ORDER_SERVICE_VARIANT_T');

		return array(
			'SELF_PICKUP'   => substr($this->fields['SERVICE_VARIANT'], 0, 1) == $T,
			'SELF_DELIVERY' => substr($this->fields['SERVICE_VARIANT'], 1, 1) == $T,
		);
	}

	public function isSelfPickup()
	{
		$serviceVariant = $this->getServiceVariant();
		return $serviceVariant['SELF_PICKUP'];
	}

	public function isSelfDelivery()
	{
		$serviceVariant = $this->getServiceVariant();
		return $serviceVariant['SELF_DELIVERY'];
	}

	/**
	 * Возвращает текстовое описание статуса заказа
	 *
	 * @return string
	 */
	public function getOrderStatusText()
	{
		$statusList = static::StatusList();
		$ret = $statusList[$this->orderStatus];

		if ($this->orderStatus == DpdOrder::STATUS_ERROR) {
			$ret .= ': '. $this->orderError;
		}

		return $ret;
	}

	/**
	 * Возвращает текстовое представление местоположения отправителя
	 *
	 * @return string
	 */
	public function getSenderLocationText()
	{
		$arLocation = $this->getShipment()->getSender();

		return implode(', ', array_filter(array_unique(array(
			'COUNTRY_NAME' => $arLocation['COUNTRY_NAME'],
			'REGION_NAME'  => $arLocation['REGION_NAME'],
			'CITY'         => $arLocation['CITY_NAME'],
		))));
	}

	/**
	 * Возвращает текстовое представление местоположения получателя
	 *
	 * @return string
	 */
	public function getReceiverLocationText()
	{
		$arLocation = $this->getShipment()->getReceiver();

		return implode(', ', array_filter(array_unique(array(
			'COUNTRY_NAME' => $arLocation['COUNTRY_NAME'],
			'REGION_NAME'  => $arLocation['REGION_NAME'],
			'CITY'         => $arLocation['CITY_NAME'],
		))));
	}

	/**
	 * Возвращает ифнормацию о тарифе
	 *
	 * @param  boolean $forced пересоздать ли экземпляр отгрузки
	 *
	 * @return \Bitrix\Main\Result
	 */
	public function getTariffDelivery($forced = false)
	{
		$result = new Result();

		$tariff = $this->getShipment($forced)->calculator()->calculateWithTariff($this->serviceCode, $this->currency);
		if (!$tariff) {
			$result->addError(new Error(Loc::getMessage('IPOLH_DPD_ORDER_GET_TARIFF_ERROR')));
		} else {
			$result->setData($tariff);
		}

		return $result;
	}

	/**
	 * Возвращает стоимость доставки в заказе
	 *
	 * @return float
	 */
	public function getActualPriceDelivery()
	{
		$result = $this->getTariffDelivery();
		if ($result->isSuccess()) {
			$tariff = $result->getData();

			return $tariff['COST'];
		}

		return false;
	}

	/**
	 * Возвращает оплаченную сумму заказа
	 * @return float
	 */
	public function getPayedPrice()
	{
		return $this->orderFields['SUM_PAID'];
	}

	/**
	 * Возвращает валюту заказа
	 *
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->orderFields['CURRENCY'];
	}

	/**
	 * Сеттер для номера заказа, попутно устанавливаем номер отправления
	 *
	 * @param $orderNum
	 */
	public function setOrderNum($orderNum)
	{
		$this->fields['ORDER_NUM']         = $orderNum;
		$this->fields['ORDER_DATE_CREATE'] = $orderNum ? \ConvertTimeStamp(false, "FULL") : '';

		if (!empty($orderNum)
			&& Option::get(IPOLH_DPD_MODULE, 'SET_TRACKING_NUMBER')
		) {
			\CSaleOrder::Update($this->orderId, array(
				'TRACKING_NUMBER' => $this->fields['ORDER_NUM'],

				// 'DELIVERY_DOC_NUM'  => $this->fields['ORDER_NUM'],
				// 'DELIVERY_DOC_DATE' => $this->fields['ORDER_DATE_CREATE'],
			));
		}
	}

	/**
	 * Сеттер для статуса заказа
	 * попутно выставляем статус битриксового заказа
	 */
	public function setOrderStatus($orderStatus, $orderStatusDate = false)
	{
		if (empty($orderStatus)) {
			return;
		}

		if (!array_key_exists($orderStatus, self::StatusList())) {
			return;
			// throw new SystemException("Is not a valid order status {$orderStatus}");
		}

		$this->fields['ORDER_STATUS'] = $orderStatus;
		$this->fields['ORDER_DATE_STATUS'] = $orderStatusDate ?: \ConvertTimeStamp(false, "FULL");

		if ($orderStatus == DpdOrder::STATUS_CANCEL) {
			$this->fields['ORDER_DATE_CANCEL'] = $orderStatusDate ?: \ConvertTimeStamp(false, "FULL");
		}

		if (!empty($orderStatus)
			&& (Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK'))
		) {
			$status = array(
				DpdOrder::STATUS_DEPARTURE        => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_PICKUP'),
				DpdOrder::STATUS_TRANSIT          => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_TRANSIT'),
				DpdOrder::STATUS_TRANSIT_TERMINAL => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_TRANSIT'),
				DpdOrder::STATUS_ARRIVE           => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_READY'),
				DpdOrder::STATUS_COURIER          => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_COURIER'),
				DpdOrder::STATUS_DELIVERED        => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_DELIVERED'),
				DpdOrder::STATUS_NOT_DONE         => Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CANCEL'),
			);

			if (isset($status[$orderStatus])
				&& !empty($status[$orderStatus])
				&& $this->orderFields['STATUS_ID'] != $status[$orderStatus]
			) {
				\CSaleOrder::StatusOrder($this->orderId, $status[$orderStatus]);
			}

			if ($this->orderFields['PAYED'] != 'Y'
				&& $orderStatus == DpdOrder::STATUS_DELIVERED
			) {
				\CSaleOrder::PayOrder($this->orderId, "Y");
			}
		}
	}

	/**
	 * @return boolean
	 */
	public function isNew()
	{
		return $this->fields['ORDER_STATUS'] == DpdOrder::STATUS_NEW;
	}

	/**
	 * Проверяет отправлялся ли заказ в DPD
	 *
	 * @return boolean
	 */
	public function isCreated()
	{
		return $this->fields['ORDER_STATUS'] != DpdOrder::STATUS_NEW
			&& $this->fields['ORDER_STATUS'] != DpdOrder::STATUS_CANCEL;
	}

	/**
	 * Проверяет отправлялся ли заказ в DPD и был ли он там успешно создан
	 *
	 * @return boolean
	 */
	public function isDpdCreated()
	{
		return $this->isCreated() && !empty($this->fields['ORDER_NUM']);
	}

	/**
	 * Возвращает инстанс для работы с внешним заказом
	 *
	 * @return \Ipolh\DPD\Order;
	 */
	public function dpd()
	{
		$locationTo = $this->getShipment()->getReceiver();
		$locationCountryCode = false;

		if (\Ipolh\DPD\API\User::isActiveAccount($locationTo['COUNTRY_CODE'])) {
			$locationCountryCode = $locationTo['COUNTRY_CODE'];
		}

		return new DpdOrder($this, \Ipolh\DPD\API\User::getInstance($locationCountryCode));
	}

	/**
	 * Загружает информацию о заказе
	 *
	 * @param  int $orderId ID заказа
	 * @return void
	 */
	protected function loadOrder($orderId = false)
	{
		$this->loadOrderFields($orderId);
		$this->loadOrderProps($orderId);
		$this->loadOrderItems($orderId);
	}

	/**
	 * Загружает поля заказа
	 *
	 * @param  int $orderId ID заказа
	 * @return void
	 */
	protected function loadOrderFields($orderId = false)
	{
		$orderId = $orderId ?: $this->ORDER_ID;
		$this->orderFields  = is_array($orderId) ? $orderId : \CSaleOrder::GetByID($orderId);
	}

	/**
	 * Загружает значения св-в заказа
	 *
	 * @param  int $orderId ID заказа
	 * @return void
	 */
	protected function loadOrderProps($orderId = false)
	{
		$orderId = $orderId ?: $this->ORDER_ID;

		$arPropValues = array();
		$rsPropValues = \CSaleOrderPropsValue::GetOrderProps($orderId);
		while($arPropValue = $rsPropValues->Fetch()) {
			$code = $arPropValue['CODE'] ?: 'PROP_'. $arPropValue['ORDER_PROPS_ID'];
			$arPropValues[$code] = $arPropValue['VALUE'];
		}

		$this->orderProps = array();
		$arProps = \Ipolh\DPD\Utils::GetOrderProps($this->orderFields['PERSON_TYPE_ID']);
		foreach ($arProps as $arProp) {
			$code  = $arProp['CODE'] ?: 'PROP_'. $arProp['ID'];
			$this->orderProps[$code] = $arPropValues[$code];
		}
	}

	/**
	 * Загружает информацию о составе заказа
	 * @return void
	 */
	protected function loadOrderItems($orderId = false)
	{
		$this->orderItems = array();

		$rsItems = \CSaleBasket::GetList(
			$arOrder  = array(),
			$arFilter = array(
				'ORDER_ID' => $orderId ?: $this->ORDER_ID,
			),
			$arGroupBy = false,
			$arNavParms = false,
			$arSelect = array(
				"ID", "PRODUCT_ID", "PRICE", "QUANTITY", "CAN_BUY",
				"DELAY", "NAME", "DIMENSIONS", "WEIGHT", "PRICE",
				"SET_PARENT_ID", "LID",
			)
		);

		while ($arItem = $rsItems->Fetch()) {
			if (!($arItem['CAN_BUY'] == 'Y' && $arItem['DELAY'] == 'N')) {
				continue;
			}

			$this->orderItems[$arItem['PRODUCT_ID']] = $arItem;
		}
	}
}