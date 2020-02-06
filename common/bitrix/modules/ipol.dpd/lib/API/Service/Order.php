<?php
namespace Ipolh\DPD\API\Service;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\API\Client\Factory as ClientFactory;

class Order
{
	protected $wdsl = 'http://ws.dpd.ru/services/order2?wsdl';

	public function __construct(User $user)
	{
		$this->client = ClientFactory::create($this->wdsl, $user);
		$this->client->setCacheTime(0);
	}

	/**
	 * Создает заказ в системе DPD
	 * 
	 * @param  array $parms
	 * @return array
	 */
	public function createOrder($parms)
	{
		return $this->client->invoke('createOrder', $parms, 'orders');
	}

	/**
	 * Отменяет заказ
	 * 
	 * @param  $internalNumber
	 * @param  $externalNumber
	 * @param  boolean $pickupDate
	 * 
	 * @return array
	 */
	public function cancelOrder($internalNumber, $externalNumber, $pickupDate = false)
	{
		return $this->client->invoke('cancelOrder', array(
			'cancel' => array_filter(array(
				'orderNumberInternal' => $internalNumber,
				'orderNum'            => $externalNumber,
				'pickupdate'          => $pickupDate,
			)),
		), 'orders');
	}

	/**
	 * Проверяет статус заказа
	 * 
	 * @param  array $parms
	 * @return array
	 */
	public function getOrderStatus($internalNumber, $pickupDate = false)
	{
		return $this->client->invoke('getOrderStatus', array(
			'order' => array_filter(array(
				'orderNumberInternal' => $internalNumber,
				'datePickup' => $pickupDate,
			)),
		), 'orderStatus');
	}

	/**
	 * Получает файл накладной
	 *
	 * Если не заданы parcelCount или cargoValue, то при формировании файла выводятся параметры из заказа.
	 * 
	 * @param  string  $orderNum    Номер заказа DPD
	 * @param  int     $parcelCount Количество мест в заказе
	 * @param  double  $cargoValue  Сумма объявленной ценности, руб.
	 * @return mixed 
	 */
	public function getInvoiceFile($orderNum, $parcelCount = false, $cargoValue = false)
	{
		$this->client->convertEncoding = false;

		$ret = $this->client->invoke('getInvoiceFile', array_filter(array(
			'orderNum'    => $orderNum,
			'parcelCount' => $parcelCount,
			'cargoValue'  => $cargoValue,
		)), 'request');

		$this->client->convertEncoding = true;

		return $ret;
	}
}