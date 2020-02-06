<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Config\Option;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\Utils;
use \Ipolh\DPD\DB\Location\Table as LocationTable;

class Shipment
{
	protected $api;

	protected $selfPickup;

	protected $selfDelivery;

	protected $declaredValue;

	protected $orderItems = array();

	protected $orderItemsPrice = 0;

	protected $dimensions = array();

	protected $paymentMethod = array();

	/**
	 * Конструктор класса
	 * 
	 * @param User $api
	 */
	public function __construct(User $api = null)
	{
		$this->api = $api ?: User::getInstance();

		$this->selfDelivery  = true;
		$this->selfPickup    = Option::get(IPOLH_DPD_MODULE, 'SELF_PICKUP', 1) > 0;
		$this->declaredValue = Option::get(IPOLH_DPD_MODULE, 'DECLARED_VALUE', 1) > 0;
	}

	/**
	 * Устанавливает местоположение отправителя
	 * 
	 * @param mixed $locationId ID местоположения
	 *
	 * @return self
	 */
	public function setSender($locationId)
	{
		$this->locationFrom = is_array($locationId) 
			? $locationId
			: LocationTable::getByLocationId($locationId)
		;

		return $this;
	}

	/**
	 * Возвращает местоположение отправителя
	 * 
	 * @return array
	 */
	public function getSender()
	{
		return $this->locationFrom;
	}

	/**
	 * Устанавливает местоположение получателя
	 * 
	 * @param mixed $locationId ID местоположения
	 */
	public function setReceiver($locationId)
	{		
		$this->locationTo = is_array($locationId)
			? $locationId
			: LocationTable::getByLocationId($locationId)
		;

		return $this;
	}

	/**
	 * Возвращает местоположение получателя
	 * 
	 * @return array
	 */
	public function getReceiver()
	{
		return $this->locationTo;
	}

	/**
	 * Устанавливает от куда будут забирать посылку
	 * true  - от терминала
	 * false - от двери
	 * 
	 * @param bool $selfPickup
	 */
	public function setSelfPickup($selfPickup)
	{
		$this->selfPickup = $selfPickup;

		return $this;
	}

	/**
	 * Возвращает флаг от куда будут забирать посылку
	 * 
	 * @return bool
	 */
	public function getSelfPickup()
	{
		return $this->selfPickup;
	}

	/**
	 * Устанавливает док куда будут забирать посылку
	 * true  - до терминала
	 * false - до двери
	 * 
	 * @param bool $selfPickup
	 */
	public function setSelfDelivery($selfDelivery)
	{
		$this->selfDelivery = $selfDelivery;

		return $this;
	}

	/**
	 * Возвращает флаг до куда будут забирать посылку
	 * 
	 * @return bool
	 */
	public function getSelfDelivery()
	{
		return $this->selfDelivery;
	}

	/**
	 * Устанавливает флаг - использовать ли объявленную ценность груза
	 * 
	 * @param bool $declaredValue
	 */
	public function setDeclaredValue($declaredValue)
	{
		$this->declaredValue = $declaredValue;

		return $this;
	}

	/**
	 * Возвращает флаг - использовать ли объявленную ценность груза
	 * 
	 * @param bool $declaredValue
	 */
	public function getDeclaredValue()
	{
		return $this->declaredValue;
	}

	/**
	 * Устанавливает список товаров для доставки
	 * 
	 * @param array   $items             список товаров
	 * @param integer $itemsPrice        сумма наложенного платежа
	 * @param array   $defaultDimensions массив с габаритами по умолчанию
	 */
	public function setItems($items, $itemsPrice = 0, $defaultDimensions = array())
	{
		// выделяем из заказа комлекты
		$complects = array();
		foreach ($items as $k => $item) {
			if (isset($item['SET_PARENT_ID'])
				&& $item['SET_PARENT_ID'] > 0
				&& $item['SET_PARENT_ID'] != $item['ID']
			) {
				$complects[] = $item['SET_PARENT_ID'];
			}
		}

		// оставляем в составе заказа только сами товары, удаляя комплекты
		$items = array_filter($items, function($item) use ($complects) { return !in_array($item['ID'], $complects); });
		$dimensions = $this->calcShipmentDimensions($items, $defaultDimensions);

		$this->orderItems = array_map(function($item) {
			return array_intersect_key($item, array_flip(array(
				'MODULE',
				'PRODUCT_ID',
				'NAME',
				'PRICE',
				'CURRENCY',
				'QUANTITY',
				'WEIGHT',
				'DIMENSIONS',
				'~DIMENSIONS',
			)));
		}, $items);

		$this->orderItemsPrice = $itemsPrice;
		$this->dimensions = $dimensions;

		return $this;
	}

	/**
	 * Возвращает список товаров в отправке
	 * 
	 * @return array
	 */
	public function getItems()
	{
		return $this->orderItems;
	}

	/**
	 * Возвращает стоимость товаров входящих в отправку
	 * 
	 * @return float
	 */
	public function getPrice()
	{
		return $this->orderItemsPrice;
	}

	/**
	 * Возвращает габариты посылки
	 * 
	 * @return array
	 */
	public function getDimensions()
	{
		return $this->dimensions;
	}

	/**
	 * Устанавливает габариты заказа
	 * 
	 * @param float $width
	 * @param float $height
	 * @param float $length
	 * @param float $weight
	 */
	public function setDimensions($width, $height, $length, $weight)
	{
		$this->dimensions['WIDTH']  = $width;
		$this->dimensions['HEIGHT'] = $height;
		$this->dimensions['LENGTH'] = $length;
		$this->dimensions['WEIGHT'] = $weight;
	}

	/**
	 * Возвращает ширину посылки, см
	 * 
	 * @return float
	 */
	public function getWidth()
	{
		return $this->dimensions['WIDTH'];
	}

	/**
	 * Устанавливает ширину посылки, см
	 * 
	 * @param float $width
	 */
	public function setWidth($width)
	{
		$this->dimensions['WIDTH'] = $width;

		return $this;
	}

	/**
	 * Возвращает высоту посылки, см
	 * 
	 * @return float
	 */
	public function getHeight()
	{
		return $this->dimensions['HEIGHT'];
	}

	/**
	 * Устанавливает высоту посылки, см
	 * 
	 * @param float $height
	 */
	public function setHeight($height)
	{
		$this->dimensions['HEIGHT'] = $height;

		return $this;
	}

	/**
	 * Возвращает длинну посылки, см
	 * 
	 * @return float
	 */
	public function getLength()
	{
		return $this->dimensions['LENGTH'];
	}

	/**
	 * Устанавливает длину посылки, см
	 * 
	 * @param float $length
	 */
	public function setLength($length)
	{
		$this->dimensions['LENGTH'] = $length;

		return $this;
	}

	/**
	 * Возвращает вес отправки, кг
	 * 
	 * @return float
	 */
	public function getWeight()
	{
		return $this->dimensions['WEIGHT'];
	}

	/**
	 * Устанавливает вес отправки, кг
	 * 
	 * @param float $weight
	 */
	public function setWeight($weight)
	{
		$this->dimensions['WEIGHT'] = $weight;

		return $this;
	}

	/**
	 * Возвращает объем отправки, м3
	 * 
	 * @return float
	 */
	public function getVolume()
	{
		$volume = $this->dimensions['WIDTH'] * $this->dimensions['HEIGHT'] * $this->dimensions['LENGTH'];

		return round($volume / 1000000, 6);
	}

	/**
	 * Устанавливает способ оплаты
	 * 
	 * @param int $personTypeId
	 * @param int $paySystemId
	 */
	public function setPaymentMethod($personTypeId, $paySystemId)
	{
		$this->paymentMethod = array(
			'PERSON_TYPE_ID' => $personTypeId,
			'PAY_SYSTEM_ID'  => $paySystemId,
		);	

		return $this;
	}

	/**
	 * Возвращает способ оплаты
	 * 
	 * @return array
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	/**
	 * Проверяет возможность осуществления доставки
	 *
	 * @return  bool
	 */
	public function isPossibileDelivery()
	{
		return $this->locationFrom && $this->locationTo;
	}

	/**
	 * Проверяет возможность осуществления в терминал доставки
	 *
	 * @return  bool
	 */
	public function isPossibileSelfDelivery($isPaymentOnDelivery = null)
	{
		if (!$this->isPossibileDelivery()) {
			return false;
		}

		$isPaymentOnDelivery = is_null($isPaymentOnDelivery) ? $this->isPaymentOnDelivery() : $isPaymentOnDelivery;

		$row = \Ipolh\DPD\DB\Terminal\Table::getList([
			'select' => array('CNT'),

			'filter' => array_filter(array_merge(
				[
					'LOCATION_ID'   => $this->locationTo['ID'],
				],

				$isPaymentOnDelivery
					? ['NPP_AVAILABLE' => 'Y', '>=NPP_AMOUNT'  => $this->getPrice()]
					: []
			)),

			'runtime' => array(
				new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
			),
		])->fetch();

		return $row['CNT'] > 0;
	}

	/**
	 * Использовать ли наложенный платеж
	 * 
	 * @return bool
	 */
	public function isPaymentOnDelivery()
	{
		$locationTo = $this->getReceiver();
		if (!User::isActiveAccount($locationTo['COUNTRY_CODE'])) {
			return false;
		}

		$payment = $this->getPaymentMethod();
		if (empty($payment)) {
			return false;
		}

		$siteId = ADMIN_SECTION ? 's1' : SITE_ID;

		if (empty($payment['PAY_SYSTEM_ID'])) {
			return (bool) Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_DEFAULT_'. $payment['PERSON_TYPE_ID'] .'_'. $siteId, 1);
		}
		
		$stPaymentIds = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_PAYMENT_'. $payment['PERSON_TYPE_ID'].'_'. $siteId);
		$arPaymentIds = \unserialize($stPaymentIds) ?: array();
		
		return in_array($payment['PAY_SYSTEM_ID'], $arPaymentIds);
	}

	/**
	 * Возвращает калькулятор для расчета стоимости доставки посылки
	 * 
	 * @return \Ipolh\DPD\Calculator
	 */
	public function calculator()
	{
		return new Calculator($this, $this->api);
	}

	/**
	 * Возвращает суммарный вес и объем товаров в заказе
	 * 
	 * @param  array $items             состав заказа
	 * @param  array $defaultDimensions значения по умолчанию, если не переданы беруться из настроек модуля
	 * @return array(weight, volume)
	 */
	protected function calcShipmentDimensions(&$items, $defaultDimensions = array())
	{
		$event = new Event(IPOLH_DPD_MODULE, "onBeforeDimensionsCount", array(&$items));
		$event->send();

		$defaultDimensions = $defaultDimensions ?: array(
			'WEIGHT' => Option::get(IPOLH_DPD_MODULE, 'WEIGHT'),
			'LENGTH' => Option::get(IPOLH_DPD_MODULE, 'LENGTH'),
			'WIDTH'  => Option::get(IPOLH_DPD_MODULE, 'WIDTH'),
			'HEIGHT' => Option::get(IPOLH_DPD_MODULE, 'HEIGHT'),
		);

		$defaultDimensions['VOLUME'] = $defaultDimensions['WIDTH'] * $defaultDimensions['HEIGHT'] * $defaultDimensions['LENGTH'];

		if ($items) {
			// получаем габариты одного вида товара в посылке с учетом кол-ва
			foreach ($items as &$item) {
				$item['DIMENSIONS'] = $item["~DIMENSIONS"] ?: $item['DIMENSIONS'];
				if (!is_array($item['DIMENSIONS'])) {
					$item['DIMENSIONS'] = unserialize($item['DIMENSIONS']);
				}

				$needCheckWeight = $needCheckWeight || $item['WEIGHT'] <= 0;
				$needCheckVolume = $needCheckDimensions || !($item['DIMENSIONS']['WIDTH'] && $item['DIMENSIONS']['HEIGHT'] && $item['DIMENSIONS']['LENGTH']);	
			}
		} else {
			$needCheckWeight = true;
			$needCheckVolume = true;
		}

		$sumDimensions = $this->sumDimensions($items);

		if ($needCheckWeight && $sumDimensions['WEIGHT'] < $defaultDimensions['WEIGHT']) {
			$sumDimensions['WEIGHT'] = $defaultDimensions['WEIGHT'];
		}

		if ($needCheckVolume && $sumDimensions['VOLUME'] < $defaultDimensions['VOLUME']) {
			$sumDimensions['WIDTH']  = $defaultDimensions['WIDTH'];
			$sumDimensions['HEIGHT'] = $defaultDimensions['HEIGHT'];
			$sumDimensions['LENGTH'] = $defaultDimensions['LENGTH'];
			// $sumDimensions['VOLUME'] = $defaultDimensions['VOLUME'];
		}

		return array(
			// мм -> см
			'WIDTH'  => $sumDimensions['WIDTH']  / 10,

			// мм -> см
			'HEIGHT' => $sumDimensions['HEIGHT'] / 10,

			// мм -> см
			'LENGTH' => $sumDimensions['LENGTH'] / 10,

			// граммы -> кг
			'WEIGHT' => $sumDimensions['WEIGHT'] / 1000,

			// мм3 -> м3
			// 'VOLUME' => $sumDimensions['VOLUME'] / 1000000000,
		);
	}

	/**
	 * Расчитывает габариты с учетом кол-ва
	 * 
	 * @param  $width
	 * @param  $height
	 * @param  $length
	 * @param  $quantity
	 * 
	 * @return array
	 */
	protected function calcItemDimensionWithQuantity($width, $height, $length, $quantity)
	{
		$ar = array($width, $height, $length);
		$qty = $quantity;
		sort($ar);

		if ($qty <= 1) {
			return array(
				'X' => $ar[0],
				'Y' => $ar[1],
				'Z' => $ar[2],
			);
		}

		$x1 = 0;
		$y1 = 0;
		$z1 = 0;
		$l  = 0;

		$max1 = floor(Sqrt($qty));
		for ($y = 1; $y <= $max1; $y++) {
			$i = ceil($qty / $y);
			$max2 = floor(Sqrt($i));
			for ($z = 1; $z <= $max2; $z++) {
				$x = ceil($i / $z);
				$l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
				if ($l == 0 || $l2 < $l) {
					$l = $l2;
					$x1 = $x;
					$y1 = $y;
					$z1 = $z;
				}
			}
		}
		
		return array(
			'X' => $x1 * $ar[0],
			'Y' => $y1 * $ar[1],
			'Z' => $z1 * $ar[2]
		);
	}

	/**
	 * Расчитывает суммарные габариты посылки
	 * 
	 * @param  array $items [description]
	 * @return array
	 */
	protected function sumDimensions($items)
	{
		$ret = array(
			'WEIGHT' => 0,
			'VOLUME' => 0,
			'LENGTH' => 0,
			'WIDTH'  => 0,
			'HEIGHT' => 0,
		);

		$a = array();
		foreach ($items as $item) {
			$a[] = self::calcItemDimensionWithQuantity(
				$item['DIMENSIONS']['WIDTH'],
				$item['DIMENSIONS']['HEIGHT'],
				$item['DIMENSIONS']['LENGTH'],
				$item['QUANTITY']
			);

			$ret['WEIGHT'] += $item['WEIGHT'] * $item['QUANTITY'];
		}

		$n = count($a);
		if ($n <= 0) { 
			return $ret;
		}

		for ($i3 = 1; $i3 < $n; $i3++) {
			// отсортировать размеры по убыванию
			for ($i2 = $i3-1; $i2 < $n; $i2++) {
				for ($i = 0; $i <= 1; $i++) {
					if ($a[$i2]['X'] < $a[$i2]['Y']) {
						$a1 = $a[$i2]['X'];
						$a[$i2]['X'] = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a1;
					};

					if ($i == 0 && $a[$i2]['Y']<$a[$i2]['Z']) {
						$a1 = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a[$i2]['Z'];
						$a[$i2]['Z'] = $a1;
					}
				}

				$a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // сумма сторон
			}

			// отсортировать грузы по возрастанию
			for ($i2 = $i3; $i2 < $n; $i2++) {
				for ($i = $i3; $i < $n; $i++) {
					if ($a[$i-1]['Sum'] > $a[$i]['Sum']) {
						$a2 = $a[$i];
						$a[$i] = $a[$i-1];
						$a[$i-1] = $a2;
					}
				}
			}

			// расчитать сумму габаритов двух самых маленьких грузов
			if ($a[$i3-1]['X'] > $a[$i3]['X']) {
				$a[$i3]['X'] = $a[$i3-1]['X'];
			}

			if ($a[$i3-1]['Y'] > $a[$i3]['Y']) { 
				$a[$i3]['Y'] = $a[$i3-1]['Y'];
			}

			$a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
			$a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // сумма сторон
		}

		return array_merge($ret, array(
			'LENGTH' => $length = Round($a[$n-1]['X'], 2),
			'WIDTH'  => $width  = Round($a[$n-1]['Y'], 2),
			'HEIGHT' => $height = Round($a[$n-1]['Z'], 2),
			'VOLUME' => $width * $height * $length,
		));
	}
} 