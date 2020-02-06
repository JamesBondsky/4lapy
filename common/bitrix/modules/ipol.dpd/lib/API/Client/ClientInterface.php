<?php
namespace Ipolh\DPD\API\Client;

interface ClientInterface
{
	/**
	 * Выполняет запрос к внешнему API
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @param  string $wrap
	 * @return mixed
	 */
	public function invoke($method, array $args = array(), $wrap = 'request');
}