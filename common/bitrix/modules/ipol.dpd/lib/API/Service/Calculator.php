<?php
namespace Ipolh\DPD\API\Service;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\API\Client\Factory as ClientFactory;

class Calculator
{
	protected $wdsl = 'http://ws.dpd.ru/services/calculator2?wsdl';

	public function __construct(User $user)
	{
		$this->client = ClientFactory::create($this->wdsl, $user);
	}

	/**
	 * Рассчитать общую стоимость доставки по России и странам ТС.
	 * 
	 * @param  array  $parms
	 * @return array
	 */
	public function getServiceCost(array $parms)
	{
		return $this->client->invoke('getServiceCost2', $parms, 'request', 'serviceCode');
	}

	/**
	 * Рассчитать стоимость доставки по параметрам  посылок по России и странам ТС.
	 * 
	 * @param  array  $parms
	 * @return array
	 */
	public function getServiceCostByParcels(array $parms)
	{
		return $this->client->invoke('getServiceCostByParcels2', $parms, 'request');
	}

	/**
	 * Рассчитать общую стоимость доставки по международным направлениям
	 * 
	 * @param  array  $parms
	 * @return array
	 */
	public function getServiceCostInternational(array $parms)
	{
		return $this->client->invoke('getServiceCostInternational', $parms, $request);
	}
}