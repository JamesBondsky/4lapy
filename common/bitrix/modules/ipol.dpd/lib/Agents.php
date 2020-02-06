<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Config\Option;
use \Ipolh\DPD\API\User as API;

class Agents
{
	/**
	 * Проверяет статусы заказов
	 * 
	 * @return string
	 */
	public static function checkOrderStatus()
	{
		global $USER;

 		if (!is_object($USER)) {
  			$USER = new \CUser();
  		}

		self::checkPindingOrderStatus();
		self::checkTrakingOrderStatus();

		return __METHOD__ .'();';
	}

	/**
	 * Проверяет статусы заказов ожидающих проверки
	 * 
	 * @return void
	 */
	protected static function checkPindingOrderStatus()
	{
		$orders = \Ipolh\DPD\DB\Order\Table::getList(array(
			'filter' => array(
				'=ORDER_STATUS' => \Ipolh\DPD\Order::STATUS_PENDING,
			),

			'order' => array(
				'ORDER_DATE_STATUS' => 'ASC',
				'ORDER_DATE_CREATE' => 'ASC',
			),

			'limit' => 2,
		));

		while($order = $orders->Fetch()) {
			$order = new \Ipolh\DPD\DB\Order\Model($order);
			$order->dpd()->checkStatus();
		}
	}

	/**
	 * Проверяет статусы заказов прошедшие проверку
	 * 
	 * @return void
	 */
	protected static function checkTrakingOrderStatus()
	{
		if (!Option::get(IPOLH_DPD_MODULE, 'STATUS_ORDER_CHECK')) {
			return;
		}

		do {
			$ret = API::getInstance()->getService('tracking')->getStatesByClient();
			if (!$ret) {
				return;
			}

			$states = (array) $ret['STATES'];
			$states = array_key_exists('DPD_ORDER_NR', $states) ? array($states) : $states;

			// сортируем статусы по их времени наступления
			uasort($states, function($a, $b) {
				if ($a['CLIENT_ORDER_NR'] == $b['CLIENT_ORDER_NR']) {
					$time1 = strtotime($a['TRANSITION_TIME']);
					$time2 = strtotime($b['TRANSITION_TIME']);

					return $time1 - $time2;
				}

				return $a['CLIENT_ORDER_NR'] - $b['CLIENT_ORDER_NR'];
			});

			foreach ($states as $state) {
				$order = \Ipolh\DPD\DB\Order\Table::findByOrder($state['CLIENT_ORDER_NR']);
				if (!$order) {
					continue;
				}

				$status = $state['NEW_STATE'];
				$statusTime = \FormatDate('d.m.Y H:i:s', strtotime($state['TRANSITION_TIME']));
				
				if ($order->isSelfDelivery()
					&& $status == \Ipolh\DPD\Order::STATUS_TRANSIT_TERMINAL
					&& $order->receiverTerminalCode == $state['TERMINAL_CODE']
				) {
					$status = \Ipolh\DPD\Order::STATUS_ARRIVE;
				}

				$order->setOrderStatus($status, $statusTime);
				$order->orderNum = $state['DPD_ORDER_NR'] ?: $order->orderNum;
				$order->save();
			}

			if ($ret['DOC_ID'] > 0) {
				API::getInstance()->getService('tracking')->confirm($ret['DOC_ID']);
			}
		} while($ret['RESULT_COMPLETE'] != 1);
	}

	/**
	 * Загружает в локальную БД данные о местоположениях и терминалах
	 * 
	 * @return string
	 */
	public static function loadExternalData()
	{
		$currStep = Option::get(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_STEP');
		$position = Option::get(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_POSITION');

		switch ($currStep) {
			case 'LOAD_LOCATION_ALL':
				$ret      = \Ipolh\DPD\DB\Location\Agent::loadAll($position);
				$nextStep = 'LOAD_LOCATION_CASH_PAY';

			break;

			case 'LOAD_LOCATION_CASH_PAY':
				$ret      = \Ipolh\DPD\DB\Location\Agent::loadCashPay($position);
				$nextStep = 'LOAD_TERMINAL_UNLIMITED';

			break;

			case 'LOAD_TERMINAL_UNLIMITED':
				$ret      = \Ipolh\DPD\DB\Terminal\Agent::loadUnlimited($position);
				$nextStep = 'LOAD_TERMINAL_LIMITED';
			break;

			case 'LOAD_TERMINAL_LIMITED':
				$ret      = \Ipolh\DPD\DB\Terminal\Agent::loadLimited($position);
				$nextStep = 'LOAD_FINISH';

			break;
			
			default:
				$ret      = true;
				$nextStep = 'LOAD_LOCATION_ALL';

				Option::set(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA', 'Y');
			break;
		}

		$nextStep = is_bool($ret) ? $nextStep : $currStep;
		$position = is_bool($ret) ? ''        : $ret;

		Option::set(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_STEP', $nextStep);
		Option::set(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_POSITION', $position);

		return __METHOD__ .'();';
	}
}