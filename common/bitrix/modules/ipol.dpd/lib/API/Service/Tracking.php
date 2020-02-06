<?php
namespace Ipolh\DPD\API\Service;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\API\Client\Factory as ClientFactory;

class Tracking
{
	protected $wdsl = 'http://ws.dpd.ru/services/tracing?wsdl';

	public function __construct(User $user)
	{
		$this->client = ClientFactory::create($this->wdsl, $user);
		$this->client->setCacheTime(0);
	}

	/**
	 * Возвращает трекинг статусы
	 * 
	 * @return array
	 */
	public function getStatesByClient()
	{
		return $this->client->invoke('getStatesByClient');
	}

	/**
	 * Подтверждает получение статусов
	 * 
	 * @param  $docId
	 * 
	 * @return array
	 */
	public function confirm($docId)
	{
		return $this->client->invoke('confirm', array(
			'docId' => $docId
		));
	}
}