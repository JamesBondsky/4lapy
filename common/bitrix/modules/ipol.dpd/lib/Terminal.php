<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Terminal implements \ArrayAccess
{
	protected $fields = array();

	/**
	 * Конструктор класса
	 * 
	 * @param array $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $this->normalize($fields);
	}

	/**
	 * Проверяет параметры посылки
	 * 
	 * @param  Shipment $shipment
	 * @param  boolean  $checkLocation
	 * @return bool
	 */
	public function checkShipment(Shipment $shipment, $checkLocation = true)
	{
		if ($checkLocation 
			&& !$this->checkLocation($shipment->getReceiver())
		) {
			return false;
		}

		if ($shipment->isPaymentOnDelivery() 
			&& !$this->checkShipmentPayment($shipment)
		) {
			return false;
		}

		if (!$this->checkShipmentDimessions($shipment)) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяет местоположение терминала и переданного местоположения
	 * 
	 * @param  array  $location
	 * @return bool
	 */
	public function checkLocation(array $location)
	{
		return $this->fields['ADDRESS']['REGION_CODE'] == $location['REGION_CODE']
			&& $this->fields['ADDRESS']['CITY_NAME'] == $location['CITY_NAME'];
	}

	/**
	 * Возвращает возможность НПП на терминале
	 * 
	 * @param  Shipment $shipment
	 * @return bool
	 */
	public function checkShipmentPayment(Shipment $shipment)
	{
		$nppExists = false;
		$extraServices = array_key_exists('ES_CODE', $this->fields['EXTRA_SERVICE']) ? array($this->fields['EXTRA_SERVICE']) : $this->fields['EXTRA_SERVICE'];
		foreach ($extraServices as $extraService) {
			if ($extraService['ES_CODE'] == Loc::getMessage('IPOLH_DPD_OPT_NPP')
				&& $extraService['PARAMS']['VALUE'] >= $shipment->getPrice()
			) {
				$nppExists = true;
				break;
			}
		}

		if ($nppExists && ($this->fields['SCHEDULE']['PAYMENT_CASH'] || $this->fields['SCHEDULE']['PAYMENT_CASHLESS'])) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяет габариты посылки
	 * 
	 * @param  Shipment $shipment
	 * @return bool
	 */
	public function checkShipmentDimessions(Shipment $shipment)
	{
		if (!$this->fields['LIMITS']) {
			return true;
		}

		$dimensions = $shipment->getDimensions();

		if ($dimensions['WEIGHT'] > $this->fields['LIMITS']['MAX_WEIGHT']) {
			return false;
		}

		$maxVolume = $this->fields['LIMITS']['MAX_WIDTH'] * $this->fields['LIMITS']['MAX_HEIGHT'] * $this->fields['LIMITS']['MAX_LENGTH'];
		$maxVolume = round($maxVolume / 1000000, 3);

		if ($shipment->getVolume() > $maxVolume) {
			return false;
		}

		$sum = $dimensions['WIDTH'] + $dimensions['HEIGHT'] + $dimensions['LENGTH'];
		if ($sum > $this->fields['LIMITS']['DIMENSION_SUM']) {
			return false;
		}

		return true;
	}

	/**
	 * Возвращает данные терминала в виде массива
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return $this->fields;
	}

	public function offsetExists($index)
	{
		return array_key_exists($index, $this->fields);
	}

	public function offsetUnset($index)
	{
		unset($this->fields[$index]);
	}

	public function offsetGet($index)
	{
		return $this->fields[$index];
	}

	public function offsetSet($index, $value)
	{
		$this->fields[$index] = $value;
	}

	/**
	 * Приводит данные терминала к единому виду
	 * 
	 * @param  array $fields
	 * @return array
	 */
	protected function normalize($fields)
	{
		return array_merge($fields, array(
			'CODE'          => $fields['TERMINAL_CODE'] ?: $fields['CODE'],
			'NAME'          => $fields['TERMINAL_NAME'] ?: $this->normalizeAddress($fields['ADDRESS'], true),
			'ADDRESS_SHORT' => $this->normalizeAddress($fields['ADDRESS'], true),
			'ADDRESS_FULL'  => $this->normalizeAddress($fields['ADDRESS']),
			'ADDRESS_DESCR' => $fields['ADDRESS']['DESCRIPT'],
			'SCHEDULE'      => array(
				'SELF_PICKUP'      => $this->normalizeSchedule($fields['SCHEDULE'], 'SelfPickup'),
				'SELF_DELIVERY'    => $this->normalizeSchedule($fields['SCHEDULE'], 'SelfDelivery'),
				'PAYMENT_CASH'     => $this->normalizeSchedule($fields['SCHEDULE'], 'Payment'),
				'PAYMENT_CASHLESS' => $this->normalizeSchedule($fields['SCHEDULE'], 'PaymentByBankCard'),
			),
		));
	}

	/**
	 * Возвращает адрес терминала в виде строки
	 * 
	 * @param  array  $address
	 * @return string
	 */
	protected function normalizeAddress($address, $short = false)
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
	protected function normalizeSchedule($schedule, $operation)
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
}