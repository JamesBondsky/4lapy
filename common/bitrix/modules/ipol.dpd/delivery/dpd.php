<?php
namespace Ipolh\DPD\Delivery;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\SystemException;

use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\Shipment;
use \Ipolh\DPD\Utils;

Loader::includeModule('ipol.dpd');
Loc::loadMessages(__FILE__);

class DPD
{
	const PICKUP_PROFILE_ID  = 'PICKUP';

	const COURIER_PROFILE_ID = 'COURIER';

	public static $needIncludeComponent = true;

	protected static $shipment;

	protected static $pickupComponent = false;

	protected static $userPaySystemId = false;

	public static function callback($method)
	{
		return array(__CLASS__, $method);
	}

	public static function getDeliveryId($deliveryCode)
	{
		if (is_numeric($deliveryCode)) {
			return $deliveryCode;
		}

		$arDelivery = \Bitrix\Sale\Delivery\Services\Table::getList(array(
			'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
			'filter' => array('CODE' => $deliveryCode)
		))->Fetch();

		return $arDelivery['ID'];
	}

	public static function getDeliveryCode($deliveryCode)
	{
		if (is_numeric($deliveryCode)) {
			$arDelivery = \Bitrix\Sale\Delivery\Services\Table::getList(array(
				'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
				'filter' => array('ID' => $deliveryCode)
			))->Fetch();

			$deliveryCode = $arDelivery['CODE'];
		}

		return $deliveryCode;
	}

	public static function isSelfInstance($deliveryCode)
	{
		$deliveryCode = static::getDeliveryCode($deliveryCode);

		return strpos($deliveryCode, 'ipolh_dpd:') !== false;
	}

	public static function getDeliveryProfile($deliveryCode)
	{
		$deliveryCode = static::getDeliveryCode($deliveryCode);

		if (!static::isSelfInstance($deliveryCode)) {
			return false;
		}

		list(, $profile) = explode(':', $deliveryCode);

		return $profile;
	}

	/**
	 * Возвращает параметры обработчика
	 */
	public function Init()
	{
		return array(
			"SID"               => "ipolh_dpd",
			"NAME"              => "DPD",
			"DESCRIPTION"       => Loc::getMessage('IPOLH_DPD_PROFILE_DESCRIPTION'),
			"DESCRIPTION_INNER" => Loc::getMessage('IPOLH_DPD_PROFILE_DESCRIPTION_INNER'),

			// базовая валюта доставки = валюте аккаунта
			// через который идет расчет
			"BASE_CURRENCY"     => \Ipolh\DPD\API\User::getInstance()->getClientCurrency(),
			"HANDLER"           => __FILE__,

			"DBGETSETTINGS"     => static::callback("GetSettings"),
			"DBSETSETTINGS"     => static::callback("SetSettings"),
			// "GETCONFIG"         => static::callback("GetConfig"),

			"COMPABILITY"       => static::callback("Compability"),
			"CALCULATOR"        => static::callback("Calculate"),

			"PROFILES"          => array(
				// ... - терминал
				self::PICKUP_PROFILE_ID => array(
					"TITLE"                       => Loc::getMessage('IPOLH_DPD_PROFILE_PICKUP_TITLE'),
					"DESCRIPTION"                 => Loc::getMessage('IPOLH_DPD_PROFILE_PICKUP_DESCRIPTION'),
					"RESTRICTIONS_WEIGHT"         => array(0),
					"RESTRICTIONS_SUM"            => array(0),
					"RESTRICTIONS_MAX_SIZE"       => "",
					"RESTRICTIONS_DIMENSIONS_SUM" => "",
				),

				// ... - дверь
				self::COURIER_PROFILE_ID => array(
					"TITLE"                       => Loc::getMessage('IPOLH_DPD_PROFILE_COURIER_TITLE'),
					"DESCRIPTION"                 => Loc::getMessage('IPOLH_DPD_PROFILE_COURIER_DESCRIPTION'),
					"RESTRICTIONS_WEIGHT"         => array(0),
					"RESTRICTIONS_SUM"            => array(0),
					"RESTRICTIONS_MAX_SIZE"       => "",
					"RESTRICTIONS_DIMENSIONS_SUM" => "",
				),
			)
		);
	}

	/**
	 * Возвращает настройки обработчика доставки
	 */
	public function GetConfig()
	{
		return array(
			'CONFIG_GROUPS' => array(),
			'CONFIG' => array(),
		);
	}

	/**
	 * Конвертиует настройки в формат для работы в скрипте
	 *
	 * @param string $strSettings
	 */
	public function GetSettings($strSettings)
	{
		return unserialize($strSettings);
	}

	/**
	 * Конвертирует настройки в формат для сохранения в БД
	 *
	 * @param array $arSettings
	 */
	public function SetSettings($arSettings)
	{
		return serialize($arSettings);
	}

	/**
	 * Метод проверки совместимости профилей с данными заказа
	 *
	 * @param array $arOrder
	 * @param array $arConfig
	 */
	public function Compability($arOrder, $arConfig)
	{
		$shipment = self::makeShipment($arOrder);

		if ($shipment->isPossibileSelfDelivery()) {
			$profiles = ['COURIER', 'PICKUP'];
		} elseif ($shipment->isPossibileDelivery()) {
			$profiles = ['COURIER'];
		} else {
			$profiles = [];
		}

		$event = new Event(IPOLH_DPD_MODULE, "onCompabilityBefore", array($profiles, $arOrder, $arConfig));
		$event->send();

		foreach($event->getResults() as $eventResult) {
			if ($eventResult->getType() != EventResult::SUCCESS) {
				continue;
			}

			$profiles = array_unique($eventResult->getParameters());
		}

		return $profiles;
	}

	/**
	 * Метод расчета стоимости доставки
	 *
	 * @param string  $profile
	 * @param array   $arConfig
	 * @param array   $arOrder
	 * @param integer $STEP
	 * @param boolean $TEMP
	 */
	public function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
	{
		try {
			$event = new Event(IPOLH_DPD_MODULE, 'onBeforeRequestDelivery', array($profile));
			$event->send();

			$shipment = static::makeShipment($arOrder);
			$shipment->setSelfDelivery($profile == self::PICKUP_PROFILE_ID);
			$shipment->setPaymentMethod($arOrder['PERSON_TYPE_ID'], static::getPaySystemId());

			$tariff = $shipment->calculator()->calculate();
			if (!$tariff) {
				throw new SystemException(Loc::getMessage("IPOLH_DPD_DELIVERY_ERROR"));
			}

			$result = array(
				"RESULT"     => "OK",
				"VALUE"      => $tariff['COST'],
				"DPD_TARIFF" => $tariff,
				"TRANSIT"    => Loc::getMessage('IPOLH_DPD_DELIVERY_DAY', array(
					'#DAYS#'        => $tariff['DAYS'],
					'#TARIFF_NAME#' => $tariff['SERVICE_NAME'],
				)),
			);

			$event = new Event(IPOLH_DPD_MODULE, 'onCalculate', array(&$result, $profile, $arConfig, $arOrder));
			$event->send();

			$result['TRANSIT'] .= self::getHiddenHTML($shipment, $tariff, $profile);

		} catch (SystemException $e) {
			$result = array(
				"RESULT" => "ERROR",
				"TEXT"   => $e->getMessage(),
			);
		}

		return $result;
	}

	/**
	 * @param  array $arOrder
	 * @param  array $arConfig
	 * @return \Ipolh\DPD\Shipment
	 */
	protected static function makeShipment($arOrder = false)
	{
		if (!self::$shipment || $arOrder) {
			self::$shipment = new Shipment();
			self::$shipment
			    ->setSender(Utils::getSaleLocationId())
			    ->setReceiver($arOrder['LOCATION_TO'])
			    ->setItems($arOrder['ITEMS'], $arOrder['PRICE']);
		}

		return self::$shipment;
	}

	/**
	 * Подключает компонент выбора ПВЗ на страницу
	 *
	 * @param  \Ipolh\DPD\Shipment $shipment [description]
	 * @return void
	 */
	protected static function includePickupComponent(Shipment $shipment)
	{
		self::getPickupComponent($shipment);
	}

	/**
	 * Возвращает экземпляр компонента выбора ПВЗ
	 *
	 * @param  \Ipolh\DPD\Shipment $shipment
	 * @return \IpolhDpdPickupComponent
	 */
	protected static function getPickupComponent(Shipment $shipment)
	{
		if (!self::needIncludeComponent()) {
			return ;
		}

		if (!self::$pickupComponent) {
			// грязный хак, никак иначе инициализировать компонент по простому не получилось
			self::$pickupComponent = $GLOBALS['APPLICATION']->includeComponent('ipol:ipol.dpdPickup', 'order', array(
				'LAZY_LOAD'      => true,
				'SHIPMENT'       => $shipment,
				'CACHE_TYPE'     => 'N',
				'CACHE_TIME'     => 0,
			));
		}

		return self::$pickupComponent;
	}

	/**
	 * Возвращает скрытые поля с параметрами доставки
	 *
	 * @param  \Ipolh\DPD\Shipment $shipment
	 * @param  array               $tariff   текущий тариф
	 * @return string
	 */
	protected static function getHiddenHTML($shipment, $tariff, $profile)
	{
		if (!self::needIncludeComponent()) {
			return '';
		}

		$_SESSION['IPOLH_DPD_ORDER'] = 'Y';
		$_SESSION['IPOLH_DPD_TARIFF'][$profile] = $tariff['SERVICE_CODE'];

		$html = ''
			. '<input type="hidden" id="IPOLH_DPD_ORDER" name="IPOLH_DPD_ORDER" value="Y">'
			. '<input type="hidden" id="IPOLH_DPD_TARIFF" name="IPOLH_DPD_TARIFF['. $profile .']" value="'. $tariff['SERVICE_CODE'] .'">';

		if (!$shipment->getSelfDelivery()) {
			return $html;
		}

		$component = self::getPickupComponent($shipment);
		$component->setShipment($shipment);

		$terminalCode = $_REQUEST['order']['IPOLH_DPD_TERMINAL'] ?: $_REQUEST['IPOLH_DPD_TERMINAL'];

		$arPaymentData     = $shipment->getPaymentMethod();
		$terminalFieldCode = Option::get(IPOLH_DPD_MODULE, 'RECEIVER_PVZ_FIELD_'. $arPaymentData['PERSON_TYPE_ID']);
		
		$arProp = \CSaleOrderProps::GetList([], ['PERSON_TYPE_ID' => $arPaymentData['PERSON_TYPE_ID'], 'CODE' => $terminalFieldCode])->Fetch();
		$terminalFieldId = $arProp['ID'];

		$html = $html . sprintf(''
			. '<input type="hidden" id="IPOLH_DPD_TERMINAL" name="IPOLH_DPD_TERMINAL['. $profile .']" value="'. $terminalCode .'">'
			. '<input type="hidden" id="IPOLH_DPD_TERMINAL_FIELD_ID" value="'. $terminalFieldId .'">'
			. '<input type="hidden" id="IPOLH_DPD_TERMINAL_FIELD_CODE" value="'. $terminalFieldCode .'">'
			. '<br>'
			. '<a class="DPD_openTerminalSelect btn btn-default btn-md" data-component-params="%s" data-component-result="%s">%s</a>',

			htmlspecialchars(
				json_encode(
					Utils::convertEncoding(
						array_filter(
							$component->arParams,
							function($key) {
								return substr($key, 0, 1) != '~';
							}
						),
						SITE_CHARSET,
						'UTF-8'
					)
				)
			),

			htmlspecialchars(
				json_encode(
					Utils::convertEncoding(
						$component->ajaxReload(),
						 SITE_CHARSET,
						 'UTF-8'
					)
				)
			),

			Loc::getMessage('IPOLH_DPD_DELIVERY_SELECT_TERMINAL')
		);

		return $html;
	}

	protected static function needIncludeComponent()
	{
		return strpos($_SERVER['REQUEST_URI'], '/admin/') === false
			&& static::$needIncludeComponent;
	}

	protected static function getPaySystemId()
	{
		if (!empty($_REQUEST['ORDER_DATA']['PAY_SYSTEM_ID'])) {
			return $_REQUEST['ORDER_DATA']['PAY_SYSTEM_ID'];
		}

		if (!empty($_REQUEST['order']['PAY_SYSTEM_ID'])) {
			return $_REQUEST['order']['PAY_SYSTEM_ID'];
		}

		if (!empty($_REQUEST['PAY_SYSTEM_ID'])) {
			return $_REQUEST['PAY_SYSTEM_ID'];
		}

		if (isset($_REQUEST['action']) 
			&& $_REQUEST['action'] == 'changeDeliveryService'
		) {
			$order_id = (int) $_REQUEST['formData']['order_id'];
			$order    = \CSaleOrder::GetByID($order_id);

			return $order ? $order['PAY_SYSTEM_ID'] : false;
		}

		// TODO: по возможности реализовать выборку первой платежной системы
		// так же как в компоненте sale.order.ajax, тогда можно было бы отказаться от
		// перезагрузки страницы

		return false;
	}

	public static function OnSaleComponentOrderOneStepProcess($arResult, $arUserResult, $arParams)
	{
		/**
		 * Если в запросе не было информации о платежной системе
		 * перезагрузим страницу для пересчета стоимости доставки
		 * с учетом выбранной платежной системой
		 *
		 * можно было бы сразу в этом событии пересчитать стоимость доставок,
		 * но в гребанном битриксе по мимо этого пришлось бы пересчитывать суммарные
		 * значения заказа (общая стоимость и т.п)
		 */
		if (!self::getPaySystemId()) {
			$GLOBALS['APPLICATION']->AddHeadString('
				<script>
					BX.ready(function() {
						(typeof window.submitForm == "function") && setTimeout(submitForm, 500);
						(typeof window.submitFormProxy == "function") && setTimeout(submitFormProxy, 500);
					});
				</script>
			');
		}

		try {
			$shipment = static::makeShipment([
				'LOCATION_FROM' => Utils::getSaleLocationId(),
				'LOCATION_TO'   => $arUserResult['DELIVERY_LOCATION'],
				'ITEMS'         => $arResult['BASKET_ITEMS'],
				'PRICE'         => $arResult['ORDER_PRICE'],
			]);

			static::includePickupComponent($shipment);
		} catch (SystemException $e) {
			// на всякий случай
		}
	}

	/**
	 * Фильтруем платежные системы по приему наложенного платежа
	 *
	 * @param $arResult
	 * @param $arUserResult
	 * @param $arParams
	 */
	public static function OnSalePaySystemFilter(&$arResult, $arUserResult, $arParams)
	{
		if ($arParams['DELIVERY_TO_PAYSYSTEM'] != 'd2p') {
			return;
		}

		if (!static::getDeliveryProfile($arUserResult['DELIVERY_ID']) !== static::PICKUP_PROFILE_ID) {
			return;
		}

		$shipment = static::makeShipment();
		if (!$shipment || !$shipment->isPossibileDelivery()) {
			return;
		}

		// если в городе есть НПП
		if ($shipment->getReceiver()['IS_CASH_PAY'] == 'Y') {
			return;
		}

		// если в городе есть терминалы которые принимаю НПП
		if ($shipment->isPossibileSelfDelivery()) {
			return;
		}

		$stPaymentIds = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_PAYMENT_'. $arUserResult['PERSON_TYPE_ID']);
		$arPaymentIds = \unserialize($stPaymentIds) ?: array();

		foreach ($arResult['PAY_SYSTEM'] as $key => $arPaySystem) {
			if (in_array($arPaySystem['PAY_SYSTEM_ID'], $arPaymentIds)) {
				unset($arResult['PAY_SYSTEM'][$key]);
			}
		}

		sort($arResult['PAY_SYSTEM']);
	}

	/**
	 * Фильтруем способы доставки по возможности оплаты нал. платежа
	 *
	 * @param $arResult
	 * @param $arUserResult
	 * @param $arParams
	 */
	public static function OnSaleDeliveryFilter(&$arResult, $arUserResult, $arParams)
	{
		if ($arParams['DELIVERY_TO_PAYSYSTEM'] != 'p2d') {
			return;
		}

		$stPaymentIds = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_PAYMENT_'. $arUserResult['PERSON_TYPE_ID']);
		$arPaymentIds = \unserialize($stPaymentIds) ?: array();

		if (!in_array($arUserResult['PAY_SYSTEM_ID'], $arPaymentIds)) {
			return;
		}

		$shipment = static::makeShipment();
		if (!$shipment || !$shipment->isPossibileDelivery()) {
			return;
		}

		$shipment->setPaymentMethod($arUserResult['PERSON_TYPE_ID'], $arUserResult['PAY_SYSTEM_ID']);
		if ($shipment->isPossibileSelfDelivery()) {
			return;
		}

		foreach ($arResult['DELIVERY'] as $deliveryId => $arDelivery) {
			if (static::getDeliveryProfile($deliveryId) === static::PICKUP_PROFILE_ID) {
				unset($arResult['DELIVERY'][$deliveryId]);
			}
		}
	}
}

\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleComponentOrderOneStepProcess', array("\\Ipolh\\DPD\\Delivery\\DPD", "OnSaleComponentOrderOneStepProcess"));
\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleComponentOrderOneStepPaySystem', array("\\Ipolh\\DPD\\Delivery\\DPD", "OnSalePaySystemFilter"));
\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', 'OnSaleComponentOrderOneStepDelivery', array("\\Ipolh\\DPD\\Delivery\\DPD", "OnSaleDeliveryFilter"));