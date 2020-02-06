<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

use \Ipolh\DPD\Utils;
use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\DB\Order\Model;

Loc::loadMessages(__FILE__);

/**
 * Класс для работы с заказом во внешней службе DPD
 */
class Order
{
	/**
	 * Новый заказ, еще не отправлялся в DPD
	 */
	const STATUS_NEW             = 'NEW';

	/**
	 * Заказ создан в DPD
	 */
	const STATUS_OK              = 'OK';

	/**
	 * Заказ требует ручной обработки
	 */
	const STATUS_PENDING         = 'OrderPending';

	/**
	 * Ошибка с заказом
	 */
	const STATUS_ERROR           = 'OrderError';

	/**
	 * Заказ отменен
	 */
	const STATUS_CANCEL          = 'Canceled';

	/**
	 * Заказ отменен ранее
	 */
	const STATUS_CANCEL_PREV     = 'CanceledPreviously';

	/**
	 * Заказ отменен
	 */
	const STATUS_NOT_DONE        = 'NotDone';

	/**
	 * Заказ принят у отпровителя
	 */
	const STATUS_DEPARTURE        = 'OnTerminalPickup';

	/**
	 * Посылка находится в пути (внутренняя перевозка DPD)
	 */
	const STATUS_TRANSIT          = 'OnRoad';

	/**
	 * Посылка находится на транзитном терминале
	 */
	const STATUS_TRANSIT_TERMINAL = 'OnTerminal';

	/**
	 * Посылка находится на терминале доставки
	 */
	const STATUS_ARRIVE           = 'OnTerminalDelivery';

	/**
	 * Посылка выведена на доставку
	 */
	const STATUS_COURIER          = 'Delivering';

	/**
	 * Посылка доставлена получателю
	 */
	const STATUS_DELIVERED        = 'Delivered';

	/**
	 * Посылка утеряна
	 */
	const STATUS_LOST             = 'Lost';

	/**
	 * с посылкой возникла проблемная ситуация 
	 */
	const STATUS_PROBLEM          = 'Problem';

	/**
	 * Посылка возвращена с доставки
	 */
	const STATUS_RETURNED         = 'ReturnedFromDelivery';

	/**
	 * Оформлен новый заказ по инициативе DPD
	 */
	const STATUS_NEW_DPD          = 'NewOrderByDPD';

	/**
	 * Оформлен новый заказ по инициативе клиента
	 */
	const STATUS_NEW_CLIENT       = 'NewOrderByClient';

	/**
	 * @var \Ipolh\DPD\DB\Order\Model
	 */
	protected $model;

	/**
	 * @var \Ipolh\DPD\API\User
	 */
	protected $api;

	/**
	 * Конструктор класса
	 * 
	 * @param \Ipolh\DPD\DB\Order\Model $model одна запись из таблицы
	 */
	public function __construct(Model $model, $api = false)
	{
		$this->model = $model;
		$this->api   = $api ?: API::getInstance();
	}

	/**
	 * Создает заказ в системе DPD
	 * 
	 * @return \Bitrix\Main\Result
	 */
	public function create()
	{
		$result = new Result();

		try {
			$GLOBALS['DB']->StartTransaction();

			$result = $this->model->save();
			if (!$result->isSuccess()) {
				throw new SystemException('Failed to save data model');
			}

			$shipment = $this->model->getShipment(true);
			if ($shipment->getSelfDelivery()) {
				$terminal = \Ipolh\DPD\DB\Terminal\Model::getByCode($this->model->receiverTerminalCode);
				
				if (!$terminal) {
					throw new SystemException(Loc::getMessage('IPOLH_DPD_TERMINAL_NOT_FOUND_ERROR'));
				}

				if ($this->model->npp == 'Y' && !$terminal->checkShipmentPayment($shipment)) {
					throw new SystemException(Loc::getMessage('IPOLH_DPD_TERMINAL_NPP_ERROR'));
				}
			}

			$parms = array(
				'HEADER' => array_filter(array(
					'DATE_PICKUP'        => Utils::DateBitrixToDpd($this->model->pickupDate),
					'SENDER_ADDRESS'     => $this->getSenderInfo(),
					'PICKUP_TIME_PERIOD' => $this->model->pickupTimePeriod,
					'REGULAR_NUM'        => Option::get(IPOLH_DPD_MODULE, 'SENDER_REGULAR_NUM', ''),
				)),

				'ORDER' => array(
					'ORDER_NUMBER_INTERNAL' => $this->model->orderId,
					'SERVICE_CODE'          => $this->model->serviceCode,
					'SERVICE_VARIANT'       => $this->model['SERVICE_VARIANT'],
					'CARGO_NUM_PACK'        => $this->model->cargoNumPack,
					'CARGO_WEIGHT'          => $this->model->cargoWeight,
					'CARGO_VOLUME'          => $this->model->cargoVolume,
					'CARGO_REGISTERED'      => $this->model->cargoRegistered == 'Y',
					// 'CARGO_VALUE'           => $this->model->cargoValue,
					'CARGO_CATEGORY'        => $this->model->cargoCategory,
					'DELIVERY_TIME_PERIOD'  => $this->model->deliveryTimePeriod,
					'RECEIVER_ADDRESS'      => $this->getReceiverInfo(),
					'EXTRA_SERVICE'         => $this->getExtraServices(),
					'UNIT_LOAD'             => $this->getUnits(),
					'PAYMENT_TYPE'          => !in_array($this->model->paymentType, ['OUP', 'OUO'])
						? null
						: Loc::getMessage('IPOLH_DPD_ORDER_PAYMENT_TYPE_'. $this->model->paymentType)
				),
			);

			$ret = $this->api->getService('order')->createOrder($parms);
			if (!in_array($ret['STATUS'], array(static::STATUS_OK, static::STATUS_PENDING))) {
				$error = 'DPD: '. nl2br($ret['ERROR_MESSAGE']);
				throw new SystemException($error);
			}

			$this->model->orderNum = $ret['ORDER_NUM'] ?: '';
			$this->model->orderStatus = $ret['STATUS'];

			$result = $this->model->save();
			if (!$result->isSuccess()) {
				throw new SystemException('Failed to save dpd order num');
			}

			$GLOBALS['DB']->Commit();
		} catch (SystemException $e) {
			$GLOBALS['DB']->Rollback();

			if ($result->isSuccess()) {
				$result = new Result();
				$result->addError(new Error($e->getMessage()));
			}
		}

		return $result;
	}

	/**
	 * Отменяет заказ в DPD
	 * 
	 * @return \Bitrix\Main\Result
	 */
	public function cancel()
	{
		$result = new Result();

		try {
			$ret = $this->api->getService('order')->cancelOrder($this->model->orderId, $this->model->orderNum, Utils::DateBitrixToDpd($this->model->pickupDate));
			if (!$ret) {
				throw new SystemException('Failed to cancel dpd order');
			}

			if (!in_array($ret['STATUS'], array(self::STATUS_CANCEL, self::STATUS_CANCEL_PREV))) {
				throw new SystemException($ret['ERROR_MESSAGE']);
			}

			$this->model->orderNum = '';
			$this->model->orderStatus = self::STATUS_CANCEL;
			$this->model->pickupDate = '';

			$result = $this->model->save();

		} catch (SystemException $e) {
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	/**
	 * Проверяет статус заказа
	 * 
	 * @return \Bitrix\Main\Result
	 */
	public function checkStatus()
	{
		$ret = $this->api->getService('order')->getOrderStatus($this->model->orderId, Utils::DateBitrixToDpd($this->model->pickupDate));

		if ($ret) {
			$this->model->orderNum = $ret['ORDER_NUM'] ?: '';
			$this->model->orderError = $ret['ERROR_MESSAGE'];
			$this->model->orderStatus = $ret['STATUS'];

			return $this->model->save();
		}

		$result = new Result();
		$result->addError(new Error(Loc::getMessage('IPOLH_DPD_ORDER_CHECK_STATUS_ERROR')));

		return $result; 
	}

	/**
	 * Запрашивает файл с наклейками DPD
	 * 
	 * @return \Bitrix\Main\Result
	 */
	public function getLabelFile($count = 1, $fileFormat = 'PDF', $pageSize = 'A5')
	{
		$result = new Result();

		try {
			if (empty($this->model->orderNum)) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_EMPTY_NUM'));
			}

			$ret = $this->api->getService('label-print')->createLabelFile($this->model->orderNum, $count, $fileFormat, $pageSize);
			if (!$ret) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_EMPTY_FILE'));
			} elseif (isset($ret['ORDER'])) {
				throw new SystemException($ret['ORDER']['ERROR_MESSAGE']);
			}

			$fileName = 'sticker.'. strtolower($fileFormat);
			$result = $this->saveFile('labelFile', $fileName, $ret['FILE']);

		} catch (SystemException $e) {
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	/**
	 * Получает файл накладной
	 * 
	 * @return \Bitrix\Main\Result;
	 */
	public function getInvoiceFile()
	{
		$result = new Result();

		try {
			if (empty($this->model->orderNum)) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_EMPTY_NUM'));
			}

			$ret = $this->api->getService('order')->getInvoiceFile($this->model->orderNum);
			if (!$ret || !isset($ret['FILE'])) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_EMPTY_FILE'));
			}

			$fileName = 'invoice.pdf';
			$result = $this->saveFile('invoiceFile', $fileName, $ret['FILE']);

		} catch (SystemException $e) {
			$result->addError(new Error($e->getMessage()));
		}

		return $result;
	}

	/**
	 * Вспомогательный метод для сохранения файла
	 * 
	 * @param  $fieldToSave
	 * @param  $fileName
	 * @param  $fileContent
	 * 
	 * @return \Bitrix\Main\Result
	 */
	protected function saveFile($fieldToSave, $fileName, $fileContent)
	{
		$result = new Result();

		try {
			if (!($dirName  = $this->getSaveDir(true))) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_MKDIR'));
			}

			$ret = file_put_contents($dirName . $fileName , $fileContent);
			if ($ret === false) {
				throw new SystemException(Loc::getMessage('IPOLH_DPD_ORDER_GET_FILE_ERROR_WRITE_FILE'));
				return $result;
			}

			$this->model->{$fieldToSave} = $this->getSaveDir() . $fileName;

			$result = $this->model->save();
			if ($result->isSuccess()) {
				$result->setData(array('file' => $this->model->{$fieldToSave}));
			}
		} catch (SystemException $e) {
			$result->addError(new Error($e->getMessage()));
		}
		
		return $result;
	}

	/**
	 * Возвращает директорию для сохранения файлов
	 * 
	 * @param  boolean $absolute
	 * 
	 * @return string
	 */
	protected function getSaveDir($absolute = false)
	{
		if (!$this->model->id) {
			return false;
		}

		$dirName = '/upload/ipol.dpd/'. $this->model->id .'/';
		$dirNameAbs = $_SERVER['DOCUMENT_ROOT'] . $dirName;

		$created = true;
		if (!is_dir($dirNameAbs)) {
			$created = mkdir($dirNameAbs, BX_DIR_PERMISSIONS, true);
		}

		if (!$created) {
			return false;
		}

		return $absolute ? $dirNameAbs : $dirName;
	}

	/**
	 * Возвращает описание адреса отправителя
	 * 
	 * @return array
	 */
	protected function getSenderInfo()
	{
		$location = $this->model->getShipment()->getSender();

		$ret = array(
			'NAME'          => $this->model->senderName,
			'CONTACT_FIO'   => $this->model->senderFio,
			'CONTACT_PHONE' => $this->model->senderPhone,
		);

		if ($this->model->getShipment()->getSelfPickup()) {
			return array_merge($ret, array(
				'TERMINAL_CODE' => $this->model->senderTerminalCode,
			));
		}

		return array_merge($ret, array(
			'COUNTRY_NAME'  => $location['COUNTRY_NAME'],
			'REGION'        => $location['REGION_NAME'],
			'CITY'          => $location['CITY_NAME'],
			'STREET'        => $this->model->senderStreet,
			'STREET_ABBR'   => $this->model->senderStreetabbr,
			'HOUSE'         => $this->model->senderHouse,
			'HOUSE_KORPUS'  => $this->model->senderKorpus,
			'STR'           => $this->model->senderStr,
			'VLAD'          => $this->model->senderVlad,
			'OFFICE'        => $this->model->senderOffice,
			'FLAT'          => $this->model->senderFlat,
		));
	}

	/**
	 * Возвращает описание адреса получателя
	 * 
	 * @return array
	 */
	protected function getReceiverInfo()
	{
		$location = $this->model->getShipment()->getReceiver();

		$ret = array(
			'NAME'          => $this->model->receiverName,
			'CONTACT_FIO'   => $this->model->receiverFio,
			'CONTACT_PHONE' => $this->model->receiverPhone,
		);

		if ($this->model->getShipment()->getSelfDelivery()) {
			return array_merge($ret, array(
				'TERMINAL_CODE' => $this->model->receiverTerminalCode,
				'INSTRUCTIONS'  => $this->model->receiverComment,
			));
		}

		return array_merge($ret, array(
			'COUNTRY_NAME'  => $location['COUNTRY_NAME'],
			'REGION'        => $location['REGION_NAME'],
			'CITY'          => $location['CITY_NAME'],
			'STREET'        => $this->model->receiverStreet,
			'STREET_ABBR'   => $this->model->receiverStreetabbr,
			'HOUSE'         => $this->model->receiverHouse,
			'HOUSE_KORPUS'  => $this->model->receiverKorpus,
			'STR'           => $this->model->receiverStr,
			'VLAD'          => $this->model->receiverVlad,
			'OFFICE'        => $this->model->receiverOffice,
			'FLAT'          => $this->model->receiverFlat,
			'INSTRUCTIONS'  => $this->model->receiverComment,
		));
	}

	/**
	 * Возвращает список доп услуг
	 * 
	 * @return array
	 */
	protected function getExtraServices()
	{
		$ret = array();

		if (!empty($this->model->sms)) {
			$ret['SMS'] = array('esCode' => 'SMS', 'param' => array('name' => 'phone', 'value' => $this->model->sms));
		}

		if (!empty($this->model->eml)) {
			$ret['EML'] = array('esCode' => 'EML', 'param' => array('name' => 'email', 'value' => $this->model->eml));
		}

		if (!empty($this->model->esd)) {
			$ret['ESD'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_ESD'), 'param' => array('name' => 'email', 'value' => $this->model->esd));
		}

		if (!empty($this->model->esz)) {
			$ret['ESZ'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_ESZ'), 'param' => array('name' => 'email', 'value' => $this->model->esz));
		}

		if ($this->model->pod != '') {
			$ret['POD'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_POD'), 'param' => array('name' => 'email', 'value' => $this->model->pod));
		}

		if ($this->model->dvd == 'Y') {
			$ret['DVD'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_DVD'), 'param' => array());
		}

		if ($this->model->trm == 'Y') {
			$ret['TRM'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_TRM'), 'param' => array());
		}

		if ($this->model->prd == 'Y') {
			$ret['PRD'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_PRD'), 'param' => array());
		}

		if ($this->model->vdo == 'Y') {
			$ret['VDO'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_VDO'), 'param' => array());
		}

		if ($this->model->ogd != '') {
			$ret['OGD'] = array('esCode' => Loc::getMessage('IPOLH_DPD_ORDER_OPT_OGD'), 'param' => array('name' => 'reason_delay', 'value' => $this->model->ogd));
		}

		return array_values($ret);
	}

	/**
	 * Возвращает список вложений для ФЗ 54
	 * 
	 * @return array
	 */
	protected function getUnits()
	{
		\CModule::IncludeModule('catalog');

		$items = $this->model->getShipment()->getItems();

		$orderAmount = $this->model->price;
		$sumNpp      = $this->model->npp == 'Y' ? $this->model->sumNpp : 0;
		$cargoValue  = $this->model->cargoValue ?: 0;

		$currencyFrom = $this->model->currency;
		$currencyTo   = $this->api->getClientCurrency();
		$currencyDate = \FormatDate('Y-m-d', \MakeTimeStamp($this->model->orderDate, 'YYYY-MM-DD HH:MI:SS'));

		$ret = array();
		foreach ($items as $item) {
			$arProduct     = \CCatalogProduct::GetByID($item['PRODUCT_ID']);
			$withOutVat    = 1;
			$vatRate       = '';
			$declaredValue = 0;
			$nppAmount     = 0;
			
			if ($arProduct['VAT_ID']) {
				$arVat = \CCatalogVat::GetByID($arProduct['VAT_ID'])->Fetch();

				if ($arVat['NAME'] != Loc::getMessage('IPOLH_DPD_ORDER_WITHOUT_VAT')) {
					$withOutVat = 0;
					$vatRate    = $arVat['RATE'];
				}
			}

			$amount         = $item['PRICE'];
			$percentInOrder = $amount * 100 / $orderAmount;

			$declaredValue = $cargoValue > 0 ? $cargoValue * $percentInOrder / 100 : 0;
			$declaredValue = \CCurrencyRates::ConvertCurrency($declaredValue, $currencyFrom, $currencyTo, $currencyDate);

			$nppAmount     = $sumNpp > 0 ? $sumNpp * $percentInOrder / 100 : 0;
			$nppAmount     = \CCurrencyRates::ConvertCurrency($nppAmount, $currencyFrom, $currencyTo, $currencyDate);

			$ret[] = array_merge(
				[
					'descript'       => $item['NAME'],
					'declared_value' => round($declaredValue, 2),
					'npp_amount'     => round($nppAmount, 2),
					'count'          => $item['QUANTITY'],
				],

				$withOutVat ? ['without_vat' => $withOutVat] : [],
				$vatRate    ? ['vat_percent' => $vatRate]    : [],

				[]
			);
		}

		return $ret;
	}
}