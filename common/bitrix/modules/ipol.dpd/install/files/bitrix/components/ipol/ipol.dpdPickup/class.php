<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;

use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\Location;
use \Ipolh\DPD\Shipment;
use \Ipolh\DPD\Utils;

Loader::includeModule('ipol.dpd');

class IpolhDpdPickupComponent extends \CBitrixComponent
{
	protected $shipment;

	public function onPrepareComponentParams($arParams = array())
	{
		if (isset($arParams['SHIPMENT'])) {
			$arParams['USER_LOCATION'] = $arParams['SHIPMENT']->getReceiver()['ID'];
			$arParams['SHOP_LOCATION'] = $arParams['SHIPMENT']->getSender()['ID'];
			$arParams['ORDER_ITEMS']   = $arParams['SHIPMENT']->getItems();
			$arParams['ORDER_PRICE']   = $arParams['SHIPMENT']->getPrice();

			unset($arParams['SHIPMENT']);
		} else {
			$arParams['USER_LOCATION'] = $arParams['USER_LOCATION'] ?: $_SESSION['IPOLH_DPD_LOCATION'];
			$arParams['SHOP_LOCATION'] = $arParams['SHOP_LOCATION'] ?: Utils::getSaleLocationId();
			$arParams['ORDER_ITEMS']   = $arParams['ORDER_ITEMS'] ?: array();
			$arParams['ORDER_PRICE']   = $arParams['ORDER_PRICE'] ?: 0;
		}

		// разрешенные местоположения
		$arParams['ALLOWED_LOCATIONS'] = is_array($arParams['ALLOWED_LOCATIONS'])
			? $arParams['ALLOWED_LOCATIONS']
			: $arParams['USER_LOCATION']
		;

		$arParams['USER_LOCATION'] = \CSaleLocation::GetByID($arParams['USER_LOCATION'], "ru")['ID'];
		$arParams['SHOP_LOCATION'] = \CSaleLocation::GetByID($arParams['SHOP_LOCATION'], "ru")['ID'];

		foreach ($arParams['ALLOWED_LOCATIONS'] as $k => $id) {
			$arBxLocation = \CSaleLocation::GetByID($id, "ru");
			$arParams['ALLOWED_LOCATIONS'][$k] = $arBxLocation['ID'];
		}

		// 
		$arParams['LAZY_LOAD'] = $arParams['LAZY_LOAD'] == 'Y' ? 'Y' : 'N';

		return parent::onPrepareComponentParams($arParams);
	}

	public function executeComponent()
	{	
		try {
			if (isset($_REQUEST['AJAX_CALL']) && isset($_REQUEST['ACTION'])) {
				return $this->processAjaxRequest();
			}

			if ($this->StartResultCache()) {
				if ($this->arParams['LAZY_LOAD'] != 'Y') {
					$this->arResult['CITIES']       = $this->getCityList();
					$this->arResult['CURRENT_CITY'] = $this->getCurrentCity();
					$this->arResult['TERMINALS']    = $this->getTerminals();
					$this->arResult['MAP_DATA']     = $this->getMapData();
					$this->arResult['TARIFFS']      = $this->getTariffs();
				}

				$this->SetResultCacheKeys(array('CITIES', 'CURRENT_CITY', 'TERMINALS', 'MAP_DATA', 'TARIFFS'));
				$this->IncludeComponentTemplate();
			}
		} catch (SystemException $e) {
			ShowError($e->getMessage());
		}

		return $this;
	}

	public function processAjaxRequest()
	{
		if (!isset($_REQUEST['ACTION'])) {
			return false;
		}

		if (!\bitrix_sessid_post()) {
			return false;
		}
		
		$method = strtoupper('ajax_'. $_REQUEST['ACTION']);
		$method = Utils::underScoreToCamelCase($method);

		if (!method_exists($this, $method)) {
			return false;
		}

		$data = $this->$method();
		
		print json_encode(array(
			'status' => is_string($data) ? 'err' : 'ok',
			'data' => $data,
		));
	}

	public function ajaxSetCity()
	{
		$cityId = $_REQUEST['city_id'];

		if (!$this->setCurrentCity($cityId)) {
			return 'City not found '. $cityId;
		}

		return $this->ajaxReload();
	}

	public function ajaxReload()
	{
		return array_merge($this->getMapData(), array(
			'TARIFFS' => $this->getTariffs(),
			'CITY_NAME' => $this->getCurrentCity()['CITY_NAME'],
		));
	}

	public function ajaxSetTerminal()
	{
		$terminalCode = $_REQUEST['terminal_code'];
		$find = false;
		foreach ($this->getTerminals() as $arTerminal) {
			if ($arTerminal['CODE'] == $terminalCode) {
				$find = true;
				break;
			}
		}

		if (!$find) {
			return 'Terminal not found';
		}

		return true;
	}

	/**
	 * Возвращает список городов
	 * 
	 * @return array
	 */
	public function getCityList()
	{
		if (isset($this->arResult['CITIES'])) {
			return $this->arResult['CITIES'];
		}

		$ret = \Ipolh\DPD\DB\Location\Table::getList([
			'filter' => [
				'LOCATION_ID' => $this->getAllowedLocations(),
			],

			'order' => 'CITY_NAME',
		]);

		$ret->addreplacedAliases(['LOCATION_ID' => 'ID']);


		return $this->arResult['CITIES'] = $ret->fetchAll();

	}

	public function getAllowedLocations()
	{
		if (isset($this->arResult['ALLOWED_LOCATIONS'])) {
			return $this->arResult['ALLOWED_LOCATIONS'];
		}

		$shipment = $this->getShipment();
		$items = \Ipolh\DPD\DB\Terminal\Table::getList([
			'filter' => array_merge(
				$this->arParams['ALLOWED_LOCATIONS'] 
					? ['LOCATION_ID' => $this->arParams['ALLOWED_LOCATIONS']] 
					: [],

				$shipment->isPaymentOnDelivery()
					? ['NPP_AVAILABLE' => 'Y', '>=NPP_AMOUNT' => $shipment->getPrice()]
					: []
			),
			'group'  => ['LOCATION_ID'],
			'select' => ['LOCATION_ID'],
		]);

		$this->arResult['ALLOWED_LOCATIONS'] = [];

		while($item = $items->fetch()) {
			$this->arResult['ALLOWED_LOCATIONS'][] = $item['LOCATION_ID'];
		}

		return $this->arResult['ALLOWED_LOCATIONS'];
	}

	/**
	 * Устанавливает текущий город
	 * 
	 * @param mixed $cityId
	 */
	public function setCurrentCity($cityId)
	{
		$arLocation = $this->findCityById($cityId);
		if (!$arLocation) {
			return false;
		}

		$this->arParams['USER_LOCATION'] = $arLocation;
		
		unset($this->arResult['CURRENT_CITY']);
		unset($this->arResult['TERMINALS']);
		unset($this->arResult['MAP_DATA']);
		unset($this->shipment);

		return true;
	}

	/**
	 * Возвращает выбранный пользователем город доставки
	 * 
	 * @return array
	 */
	public function getCurrentCity()
	{
		if (isset($this->arResult['CURRENT_CITY'])) {
			return $this->arResult['CURRENT_CITY'];
		}

		$findLocation = false;

		$shipment = $this->getShipment();

		if ($shipment->isPossibileSelfDelivery()) {
			$location     = $shipment->getReceiver();
			$findLocation = $this->findCityById($location['CITY_CODE']) ? $location : false;
		}

		if (!$findLocation) {
			$findLocation = reset($this->getCityList());
		}

		return $this->arResult['CURRENT_CITY'] = $findLocation;
	}

	/**
	 * Ищет город по его ID
	 * 
	 * @param  int $cityId
	 * @return array
	 */
	public function findCityById($cityId)
	{
		foreach ($this->getCityList() as $arCity) {
			if ($arCity['CITY_CODE'] == $cityId) {
				return $arCity;
			}
		}

		return false;
	}

	/**
	 * Возвращает список терминалов для доставки
	 * 
	 * @return array
	 */
	public function getTerminals()
	{
		if (isset($this->arResult['TERMINALS'])) {
			return $this->arResult['TERMINALS'];
		}

		$shipment = $this->getShipment();

		if (!$shipment->isPossibileSelfDelivery()) {
			return array();
		}

		$items = $this->arResult['TERMINALS'] = \Ipolh\DPD\DB\Terminal\Model::getList([
			'filter' => $f = array_merge(
				[
					'LOCATION_ID' => $shipment->getReceiver()['ID'],
				],

				$shipment->isPaymentOnDelivery()
					? ['NPP_AVAILABLE' => 'Y', '>=NPP_AMOUNT' => $shipment->getPrice()]
					: []
			),

			'order' => ['NAME' => 'ASC']
		]);

		$ret = [];
		foreach ($items as $item) {
			if ($item->checkShipment($shipment)) {
				$ret[] = $item;
			}
		}

		return $ret;
	}

	/**
	 * Возвращает список параметров для карты
	 * 
	 * @return array
	 */
	public function getMapData()
	{
		if (isset($this->arResult['MAP_DATA'])) {
			return $this->arResult['MAP_DATA'];
		}

		$this->initComponentTemplate();

		$placemarks = array();
		$terminalTypes = array();
		foreach ($this->getTerminals() as $arItem) {
			ob_start();
			include($_SERVER['DOCUMENT_ROOT'] . $this->getTemplate()->getFolder() .'/placemark.php');
			$balloonContent = str_replace(PHP_EOL, '', ob_get_clean());

			$placemarks[] = array(
				'CODE'  => $arItem['ID'],
				'TITLE' => $arItem['NAME'],
				'TYPE'  => $arItem['PARCEL_SHOP_TYPE'],
				'TEXT'  => $balloonContent,
				'LAT'   => $arItem['LATITUDE'],
				'LON'   => $arItem['LONGITUDE'],
				'ADDR'  => $arItem['ADDRESS_FULL'],
			);
		}

		return $this->arResult['MAP_DATA'] = array(
			'PLACEMARKS' => $placemarks,
		);
	}

	/**
	 * Возвращает информацию о тарифах
	 * 
	 * @return array
	 */
	public function getTariffs()
	{
		if (isset($this->arResult['TARIFFS'])) {
			return $this->arResult['TARIFFS'];
		}

		$this->arResult['TARIFFS'] = [];

		\Bitrix\Sale\DiscountCouponsManager::init();

		$shipment = CSaleDelivery::convertOrderOldToNew([
			'WEIGHT'      => $this->getShipment()->getWeight(),
			'PRICE'       => $this->getShipment()->getPrice(),
			'LOCATION_TO' => $this->getShipment()->getReceiver()['ID'],
			'ITEMS'       => $this->getShipment()->getItems(),
			'CURRENCY'    => 'RUB',
		]);

		$order = $shipment->getCollection()->getOrder();
		$order->doFinalAction(true);

		// disable include component
		\Ipolh\DPD\Delivery\DPD::$needIncludeComponent = false;

		foreach (['COURIER' => false, 'PICKUP' => true] as $code => $selfDelivery) {
			$deliveryId  = \Ipolh\DPD\Delivery\DPD::getDeliveryId('ipolh_dpd:'. $code);
			$deliveryObj = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($deliveryId);

			$shipment->setField('DELIVERY_ID', $deliveryObj->getId());
			$order->getShipmentCollection()->calculateDelivery();
			$calcResult = $deliveryObj->calculate($shipment);

			if (!$calcResult->isSuccess()) {
				continue;
			}

			$deliveryPrice         = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($calcResult->getPrice(), $order->getCurrency());
			$discountDeliveryPrice = \Bitrix\Sale\PriceMaths::roundByFormatCurrency($order->getDeliveryPrice(), $order->getCurrency());

			$this->arResult['TARIFFS'][$code] = array_merge(\Ipolh\DPD\Calculator::getLastResult(), [
				'COST' => $discountDeliveryPrice && $discountDeliveryPrice != $deliveryPrice
							? $discountDeliveryPrice
							: $deliveryPrice
				,
			]);
		}

		\Ipolh\DPD\Delivery\DPD::$needIncludeComponent = true;

		return $this->arResult['TARIFFS'];
	}

	/**
	 * Возвращает инстанс доставки
	 * 
	 * @return \Ipolh\DPD\Shipment
	 */
	public function getShipment()
	{
		if (!$this->shipment) {
			$this->shipment = new Shipment();
			$this->shipment->setSender($this->arParams['SHOP_LOCATION']);
			$this->shipment->setReceiver($this->arParams['USER_LOCATION']);
			$this->shipment->setItems($this->arParams['ORDER_ITEMS'], $this->arParams['ORDER_PRICE']);
			$this->shipment->setPaymentMethod($this->arParams['PERSON_TYPE_ID'], $this->arParams['PAY_SYSTEM_ID']);
			
		}

		return $this->shipment;
	}

	/**
	 * Устанавливает параметры компонента на основе доставки
	 * 
	 * @param \Ipolh\DPD\Shipment $shipment
	 */
	public function setShipment(Shipment $shipment)
	{
		$arParams = array();

		$arParams['USER_LOCATION']      = $shipment->getReceiver()['ID'];
		$arParams['SHOP_LOCATION']      = $shipment->getSender()['ID'];
		$arParams['ORDER_ITEMS']        = $shipment->getItems();
		$arParams['ORDER_PRICE']        = $shipment->getPrice();
		$arParams['ALLOWED_LOCATIONS']  = array($arParams['USER_LOCATION']);

		if ($shipment->getPaymentMethod()) {
			$arParams = array_merge($arParams, $shipment->getPaymentMethod());
		}

		$this->shipment = $shipment;
		$this->arParams = array_merge($this->arParams, $arParams);
		$this->arResult = array();

		return $this->arParams;
	}
}