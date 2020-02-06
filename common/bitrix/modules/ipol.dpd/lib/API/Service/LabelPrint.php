<?php
namespace Ipolh\DPD\API\Service;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\API\Client\Factory as ClientFactory;

class LabelPrint
{
	protected $wdsl = 'http://ws.dpd.ru/services/label-print?wsdl';

	public function __construct(User $user)
	{
		$this->client = ClientFactory::create($this->wdsl, $user);
		$this->client->setCacheTime(0);
	}

	/**
	 * Формирует файл с наклейками DPD
	 * 
	 * @return mixed
	 */
	public function createLabelFile($orderNum, $parcelsNumber = 1, $fileFormat = 'PDF', $pageSize = 'A5')
	{
		return $this->client->invoke('createLabelFile', array(
			'fileFormat' => $fileFormat,
			'pageSize'   => $pageSize,
			'order'      => array(
				'orderNum'      => $orderNum,
				'parcelsNumber' => $parcelsNumber
			),
		), 'getLabelFile');
	}
}