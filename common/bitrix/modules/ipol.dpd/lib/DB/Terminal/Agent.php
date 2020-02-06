<?php
namespace Ipolh\DPD\DB\Terminal;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\DB\Location\Table as LocationTable;
use \Ipolh\DPD\DB\Terminal\Table as TerminalTable;
use \Ipolh\DPD\Utils;

Loc::loadMessages(__FILE__);

class Agent
{
	public static function loadUnlimited($position = 0)
	{
		global $DB;

		$position   = $position ?: 0;
		$index      = 0;
		$start_time = time();

		if ($position == 0) {
			$DB->Query('UPDATE `b_ipol_dpd_terminal` SET `UPDATE_CHECKED` = "N" WHERE `IS_LIMITED` = "N"');
		}

		$items = API::getInstance()->getService('geography')->getTerminalsSelfDelivery2() ?: array();

		foreach ($items as $item) {
			if ($index++ < $position) {
				continue;
			}

			if (Utils::isNeedBreak($start_time)) {
				return $index;
			}

			static::loadTerminal($item);
		}

		$DB->Query('DELETE FROM `b_ipol_dpd_terminal` WHERE `UPDATE_CHECKED` = "N" AND `IS_LIMITED` = "N"');

		return true;
	}

	public static function loadLimited($position = 'RU:0')
	{
		global $DB;
		
		$position   = explode(':', $position ?: 'RU:0');
		$index      = 0;
		$started    = false;
		$start_time = time();

		if ($position[0] == 'RU' && $position[1] == '0') {
			$DB->Query('UPDATE `b_ipol_dpd_terminal` SET `UPDATE_CHECKED` = "N" WHERE `IS_LIMITED` = "Y"');
		}

		foreach (['RU', 'KZ', 'BY'] as $countryCode) {
			if ($position[0] != $countryCode && $started === false) {
				continue;
			}

			$started = true;
			$items   = API::getInstance()->getService('geography')->getParcelShops($countryCode) ?: array();

			foreach ($items as $item) {
				if ($index++ < $position[1]) {
					continue;
				}

				if (Utils::isNeedBreak($start_time)) {
					return sprintf('%s:%s', $countryCode, $index);
				}

				static::loadTerminal($item);
			}
		}

		$DB->Query('DELETE FROM `b_ipol_dpd_terminal` WHERE `UPDATE_CHECKED` = "N" AND `IS_LIMITED` = "Y"');

		return true;
	}

	public static function removeAllExcept($ids, array $filter = array())
	{
		$items = TerminalTable::getList([
			'select' => ['ID'],
			'filter' => array_merge($filter, ['!ID' => $ids])
		]);

		while($item = $items->fetch()) {
			TerminalTable::delete($item['ID']);
		}
	}

	protected static function loadTerminal($item)
	{
		$arLocation = LocationTable::getByCityId($item['ADDRESS']['CITY_ID']);

		if (!$arLocation) {
			return false;
		}

		$fields = [
			'LOCATION_ID' => $arLocation['LOCATION_ID'],

			'CODE' => $item['TERMINAL_CODE'] ?: $item['CODE'],
			'NAME' => static::normalizeAddress($item['ADDRESS'], true),

			'ADDRESS_FULL'  => static::normalizeAddress($item['ADDRESS']),
			'ADDRESS_SHORT' => static::normalizeAddress($item['ADDRESS'], true),
			'ADDRESS_DESCR' => $item['ADDRESS']['DESCRIPT'],

			'PARCEL_SHOP_TYPE' => $item['PARCEL_SHOP_TYPE'],

			'SCHEDULE_SELF_PICKUP'      => implode('<br>', static::normalizeSchedule($item['SCHEDULE'], 'SelfPickup')),
			'SCHEDULE_SELF_DELIVERY'    => implode('<br>', static::normalizeSchedule($item['SCHEDULE'], 'SelfDelivery')),
			'SCHEDULE_PAYMENT_CASH'     => $paymentCash = implode('<br>', static::normalizeSchedule($item['SCHEDULE'], 'Payment')),
			'SCHEDULE_PAYMENT_CASHLESS' => $paymentCashLess = implode('<br>', static::normalizeSchedule($item['SCHEDULE'], 'PaymentByBankCard')),

			'LATITUDE'  => $item['GEO_COORDINATES']['LATITUDE'],
			'LONGITUDE' => $item['GEO_COORDINATES']['LONGITUDE'],

			'IS_LIMITED'                => 'N',
			'LIMIT_MAX_SHIPMENT_WEIGHT' => 0,
			'LIMIT_MAX_WEIGHT'          => 0,
			'LIMIT_MAX_LENGTH'          => 0,
			'LIMIT_MAX_WIDTH'           => 0,
			'LIMIT_MAX_HEIGHT'          => 0,
			'LIMIT_MAX_VOLUME'          => 0,
			'LIMIT_SUM_DIMENSION'       => 0,

			'NPP_AMOUNT'     => $maxNppAmount                                                                         = static::getMaxNppAmount($item),
			'NPP_AVAILABLE'  => (((bool) $maxNppAmount) && (((bool) $paymentCash) || ((bool) $paymentCashLess))) ? 'Y': 'N',
			'UPDATE_CHECKED' => 'Y',
		];

		if (isset($item['LIMITS'])) {
			$fields['IS_LIMITED']                = 'Y';
			$fields['LIMIT_MAX_SHIPMENT_WEIGHT'] = $item['LIMITS']['MAX_SHIPMENT_WEIGHT'] ?: 0;
			$fields['LIMIT_MAX_WEIGHT']          = $item['LIMITS']['MAX_WEIGHT']          ?: 0;
			$fields['LIMIT_MAX_LENGTH']          = $item['LIMITS']['MAX_LENGTH']          ?: 0;
			$fields['LIMIT_MAX_WIDTH']           = $item['LIMITS']['MAX_WIDTH']           ?: 0;
			$fields['LIMIT_MAX_HEIGHT']          = $item['LIMITS']['MAX_HEIGHT']          ?: 0;
			$fields['LIMIT_MAX_VOLUME']          = round($item['LIMITS']['MAX_WIDTH'] * $item['LIMITS']['MAX_HEIGHT'] * $item['LIMITS']['MAX_LENGTH'] / 1000000, 3);
			$fields['LIMIT_SUM_DIMENSION']       = $item['LIMITS']['DIMENSION_SUM']       ?: 0;
		}
		
		$exists = TerminalTable::getByCode($fields['CODE']);
		if ($exists) {
			$result = TerminalTable::update($exists['ID'], $fields);
		} else {
			$result = TerminalTable::add($fields);
		}

		return $result->isSuccess() ? ($exists ? $exists['ID'] : $result->getId()) : false;
	}

	/**
	 * Возвращает адрес терминала в виде строки
	 * 
	 * @param  array  $address
	 * @return string
	 */
	protected static function normalizeAddress($address, $short = false)
	{
		$ret = array();

		if ($short == false) {
			$ret[] = $address['INDEX'];

			if ($address['REGION_NAME'] != $address['CITY_NAME']) {
				$ret[] = $address['REGION_NAME'];
			}

			$ret[] = $address['CITY_NAME'];
		}

		$ret[] = $address['STREET'] .' '. $address['STREET_ABBR'];

		if (!empty($address['HOUSE_NO'])) {
			$ret[] = Loc::getMessage('IPOLH_DPD_ADDRESS_HOUSE') .' '. $address['HOUSE_NO'];
		}

		if (!empty($address['BUILDING'])) {
			$ret[] = Loc::getMessage('IPOLH_DPD_ADDRESS_BUILDING') .' '. $address['BUILDING'];
		}

		if (!empty($address['STRUCTURE'])) {
			$ret[] = Loc::getMessage('IPOLH_DPD_ADDRESS_STRUCTURE') .' '. $address['STRUCTURE'];
		}

		if (!empty($address['OWNERSHIP'])) {
			$ret[] = Loc::getMessage('IPOLH_DPD_ADDRESS_OWNERSHIP') .' '. $address['OWNERSHIP'];
		}

		return implode(', ', $ret);
	}

	/**
	 * Возвращает график работы терминала в виде строки
	 * 
	 * @param  array  $schedule  график работы
	 * @param  string $operation операция для фильтрации
	 * @return string
	 */
	protected static function normalizeSchedule($schedule, $operation)
	{
		$schedule = array_key_exists('OPERATION', $schedule)
			? array($schedule)
			: $schedule;


		$grouped = array();
		foreach($schedule as $item) {
			if ($item['OPERATION'] != $operation) {
				continue;
			}

			$timetable = array_key_exists('WEEK_DAYS', $item['TIMETABLE'])
				? array($item['TIMETABLE'])
				: $item['TIMETABLE']
			;

			foreach ($timetable as $data) {
				$grouped[$data['WORK_TIME']] = explode(',', $data['WEEK_DAYS']);
			}
		}

		$weekdays = array_flip(array(
			Loc::getMessage('IPOLH_DPD_WEEKDAY_MON'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_TUE'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_WED'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_THU'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_FRI'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_SAT'),
			Loc::getMessage('IPOLH_DPD_WEEKDAY_SUN'),
		));

		$ret = array();
		foreach ($grouped as $time => $days) {
			usort($days, function($a, $b) use ($weekdays) {
				return $weekdays[$a] - $weekdays[$b];
			});

			$fromDay   = reset($days);
			$fromIndex = $weekdays[$fromDay];
			$prevDay   = $fromDay;
			$prevIndex = $fromIndex;

			$timetable = '';
			foreach($days as $day) {
				$currentIndex = $weekdays[$day];
				if ($currentIndex - $prevIndex > 1) {
					$timetable .= $fromDay . ($fromDay != $prevDay ? '-'. $prevDay : '') .',';
					$fromDay = $day;
				}
				$prevDay = $day;
				$prevIndex = $currentIndex;
			}

			$ret[] = $timetable 
				. $fromDay 
				. ($fromDay != $prevDay ? '-'. $prevDay : '')
				. ': '. $time;
		}

		return $ret;
	}

	protected function getMaxNppAmount($item)
	{
		$extraServices = array_key_exists('ES_CODE', $item['EXTRA_SERVICE']) ? array($item['EXTRA_SERVICE']) : $item['EXTRA_SERVICE'];

		foreach ($extraServices as $extraService) {
			if ($extraService['ES_CODE'] == Loc::getMessage('IPOLH_DPD_OPT_NPP')) {
				return $extraService['PARAMS']['VALUE'] ?: 9999999999;
			}
		}

		return 0;
	}
}