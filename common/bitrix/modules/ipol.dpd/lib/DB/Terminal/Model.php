<?php
namespace Ipolh\DPD\DB\Terminal;

use \Bitrix\Main\Localization\Loc;
use \Ipolh\DPD\DB\Terminal\Table as TerminalTable;

Loc::loadMessages(__FILE__);

class Model implements \ArrayAccess
{
	protected $fields = array();

	/**
	 * Возвращает инстанс по коду
	 * 
	 * @param  $code
	 * @return Model
	 */
	public static function getByCode($code)
	{
		$data = TerminalTable::getByCode($code);
		if (!$data) {
			return false;
		}

		return new self($data);
	}

	public static function getList($parms)
	{
		$items = TerminalTable::getList($parms);
		$items->addReplacedAliases(['CODE' => 'ID']);

		$ret = [];
		foreach ($items->fetchAll() as $item) {
			$ret[] = new self($item);
		}

		return $ret;
	}

	/**
	 * Конструктор класса
	 * 
	 * @param array $fields
	 */
	public function __construct(array $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * Проверяет параметры посылки
	 * 
	 * @param  Shipment $shipment
	 * @param  boolean  $checkLocation
	 * @return bool
	 */
	public function checkShipment(\Ipolh\DPD\Shipment $shipment, $checkLocation = true)
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
		return $this->fields['LOCATION_ID'] == $location['ID'];
	}

	/**
	 * Возвращает возможность НПП на терминале
	 * 
	 * @param  Shipment $shipment
	 * @return bool
	 */
	public function checkShipmentPayment(\Ipolh\DPD\Shipment $shipment)
	{
		if ($this->fields['NPP_AVAILABLE'] != 'Y')  {
			return false;
		}

		return $this->fields['NPP_AMOUNT'] >= $shipment->getPrice();
	}

	/**
	 * Проверяет габариты посылки
	 * 
	 * @param  Shipment $shipment
	 * @return bool
	 */
	public function checkShipmentDimessions(\Ipolh\DPD\Shipment $shipment)
	{
		if ($this->fields['IS_LIMITED'] != 'Y') {
			return true;
		}

		return (
				$this->fields['LIMIT_MAX_WEIGHT'] <= 0
				|| $shipment->getWeight() <= $this->fields['LIMIT_MAX_WEIGHT']
			)

			&& (
				$this->fields['LIMIT_MAX_VOLUME'] <= 0
				|| $shipment->getVolume() <= $this->fields['LIMIT_MAX_VOLUME']
			)

			&& (
				$this->fields['LIMIT_SUM_DIMENSION'] <= 0
				|| array_sum([$shipment->getWidth(), $shipment->getHeight(), $shipment->getLength()]) <= $this->fields['LIMIT_SUM_DIMENSION']
			)
		;
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
}