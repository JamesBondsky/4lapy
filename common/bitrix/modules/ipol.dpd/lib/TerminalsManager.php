<?php
namespace Ipolh\DPD;

use \Bitrix\Main\SystemException;
use \Bitrix\Main\Data\Cache;

use \Ipolh\DPD\API\User;

class TerminalsManager
{
	protected $api;

	protected $cache;

	protected $cache_time = IPOLH_DPD_CACHE_TIME;

	protected static $terminals = array();

	/**
	 * @return \Ipolh\DPD\Terminal[]
	 */
	public static function getUnlimitedList()
	{
		if (isset(self::$terminals['unlimited'])) {
			return self::$terminals['unlimited'];
		}

		self::$terminals['unlimited'] = array();

		$items = User::getInstance()->getService('geography')->getTerminalsSelfDelivery2();
		foreach($items as $item) {
			$item = new Terminal($item);
			self::$terminals['unlimited'][$item['CODE']] = $item;
		}

		return self::$terminals['unlimited'];
	}

	/**
	 * @return \Ipolh\DPD\Terminal[]
	 */
	public static function getLimitedList()
	{
		if (isset(self::$terminals['limited'])) {
			return self::$terminals['limited'];
		}

		self::$terminals['limited'] = array();

		$items = User::getInstance()->getService('geography')->getParcelShops() ?: array();
		foreach($items as $item) {
			$item = new Terminal($item);
			self::$terminals['limited'][$item['CODE']] = $item;
		}

		return self::$terminals['limited'];	
	}

	/**
	 * @return \Ipolh\DPD\Terminal[]
	 */
	public static function getList()
	{
		return array_merge(self::getUnlimitedList(), self::getLimitedList());
	}

	public static function getListByLocationId($locationId)
	{
		$location = Location::getInstance()->find($locationId);
		if (!$location) {
			return array();
		}

		return self::getListByLocation($location);
	}

	public static function getListByLocation(array $location)
	{
		$ret = array();

		foreach (self::getList() as $key => $terminal) {
			if ($terminal->checkLocation($location)) {
				$ret[$key] = $terminal;
			}
		}

		return $ret;
	}

	/**
	 * @return \Ipolh\DPD\Terminal
	 */
	public static function getByCode($code)
	{
		$items = self::getList();

		if (!isset($items[$code])) {
			return false;
		}

		return $items[$code];
	}

	/**
	 * Конструктор класса
	 * 
	 * @param \Ipolh\DPD\API\User $api
	 */
	public function __construct(Shipment $shipment, User $api = null)
	{
		$this->shipment = $shipment;
		$this->api      = $api ?: User::getInstance();
	}

	/**
	 * Устанавливает посылку для поиска терминалов
	 * 
	 * @param \Ipolh\DPD\Shipment $shipment
	 */
	public function setShipment(Shipment $shipment)
	{
		$this->shipment = $shipment;

		return $this;
	}

	/**
	 * Устанавливает посылку для которой ищутся терминалы
	 * 
	 * @param \Ipolh\DPD\Shipment $shipment
	 */
	public function getShipment()
	{
		return $this->shipment;
	}

	/**
	 * Возвращает список терминалов
	 *
	 * @param  bool $ignoreLocation Не учитывать местоположение получателя
	 * 
	 * @return array
	 */
	public function getTerminals($ignoreLocation = false)
	{
		return array_merge(
			$this->getUnlimitedTerminals($ignoreLocation),
			$this->getShipment()->getDimensions() ? $this->getLimitedTerminals($ignoreLocation) : array()
		);
	}

	/**
	 * Возвращает список терминалов без ограничений 
	 * 
	 * @param $ignoreLocation Не учитывать местоположение получателя
	 * @return array
	 */
	public function getUnlimitedTerminals($ignoreLocation = false)
	{
		if (!$ignoreLocation && !$this->getShipment()->isPossibileDelivery()) {
			throw new SystemException("Service does not deliver to the city");
		}

		if (!$this->getShipment()->getSelfDelivery()) {
			return array();
		}

		$shipment    = $this->getShipment();
		$locationTo  = $this->getShipment()->getReceiver();

		$cache_path = '/'. IPOLH_DPD_MODULE .'/terminals/';
		$cache_id   = 'unlimited'
			.':'. ($ignoreLocation ? 'all' : serialize($locationTo))
			.':'. ($this->getShipment()->isPaymentOnDelivery() ? serialize($this->getShipment()->getPaymentMethod()) : 'all')
		;

		if ($this->cache()->initCache($this->cache_time, $cache_id, $cache_path) && 1 != 1) {
			return $this->cache()->GetVars();
		}

		$ret = self::getUnlimitedList();
		$ret = $this->filterList($ret, $ignoreLocation);

		if ($this->cache()->startDataCache()) {
			$this->cache()->endDataCache($ret);
		}

		return $ret;
	}

	/**
	 * Возвращает список терминалов с ограничениями
	 *
	 * @param $ignoreLocation Не учитывать местоположение получателя
	 * @return array
	 */
	public function getLimitedTerminals($ignoreLocation = false)
	{
		if (!$ignoreLocation && !$this->getShipment()->isPossibileDelivery()) {
			throw new SystemException("Service does not deliver to the city");
		}

		if (!$this->getShipment()->getSelfDelivery()) {
			return array();
		}

		$locationTo = $this->getShipment()->getReceiver();
		$dimensions = $this->getShipment()->getDimensions();

		$cache_path = '/'. IPOLH_DPD_MODULE .'/terminals/';
		$cache_id = sprintf('limited:%s:%s:%s', 
			$ignoreLocation ? 'all' : \serialize($locationTo),
			\serialize($dimensions),
			$this->getShipment()->isPaymentOnDelivery() ? serialize($this->getShipment()->getPaymentMethod()) : 'all'
		);

		if ($this->cache()->initCache($this->cache_time, $cache_id, $cache_path)) {
			return $this->cache()->GetVars();
		}

		$ret = self::getLimitedList();
		$ret = $this->filterList($ret, $ignoreLocation);
		
		if ($this->cache()->startDataCache()) {
			$this->cache()->endDataCache($ret);
		}

		return $ret;
	}

	/**
	 * Фильтрует список терминалов по параметрам посылки
	 * 
	 * @param  \Ipolh\DPD\Terminal[]  $list
	 * @param  boolean $ignoreLocation
	 * @return array
	 */
	protected function filterList($list, $ignoreLocation = false)
	{
		$ret = array();

		foreach ($list as $key => $terminal) {
			if ($terminal->checkShipment($this->getShipment(), $ignoreLocation !== true)) {
				$ret[$key] = $terminal->toArray(); 
			}
		}

		return $ret;
	}	

	

	

	

	

	/**
	 * Возвращает инстанс кэша
	 * 
	 * @return \Bitrix\Main\Data\Cache
	 */
	protected function cache()
	{
		return $this->cache ?: $this->cache = Cache::createInstance();
	}
}