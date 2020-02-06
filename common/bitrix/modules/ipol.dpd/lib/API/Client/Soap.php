<?php
namespace Ipolh\DPD\API\Client;

use \Bitrix\Main\SystemException;
use \Bitrix\Main\Data\Cache;

use \Ipolh\DPD\API\User;
use \Ipolh\DPD\Utils;

class Soap extends \SoapClient implements ClientInterface
{
	public $convertEncoding = true;

	/**
	 * Параметры авторизации
	 * @var array
	 */
	protected $auth = array();

	/**
	 * Время хранения кеша
	 * @var integer
	 */
	protected $cache_time = IPOLH_DPD_CACHE_TIME;

	/**
	 * Параметры для SoapClient
	 * @var array
	 */
	protected $soap_options = array(
		'connection_timeout' => 20,
	);

	protected $initError = false;

	/**
	 * Конструктор класса
	 * 
	 * @param string $wsdl
	 * @param User   $user
	 * @param array  $options
	 */
	public function __construct($wsdl, User $user, array $options = array())
	{
		try {
			$this->auth = array(
				'clientNumber' => $user->getClientNumber(),
				'clientKey'    => $user->getSecretKey(),
			);

			if (empty($this->auth['clientNumber'])
			    || empty($this->auth['clientKey'])
			) {
				throw new SystemException('DPD: Authentication data is not provided');
			}

			parent::__construct(
				$user->resolveWsdl($wsdl),
				array_merge($this->soap_options, $options)
			);
		} catch (\Exception $e) {
			$this->initError = $e->getMessage();
		}
	}

	/**
	 * Устанавливает время жизни кэша
	 * @param int $cacheTime
	 */
	public function setCacheTime($cacheTime)
	{
		$this->cache_time = $cacheTime;
	}

	/**
	 * Выполняет запрос к внешнему API
	 *
	 * TODO: в качестве возвращаемого результата использовать \Bitrix\Main\Result
	 * 
	 * @param  string $method
	 * @param  array  $args
	 * @param  string $wrap
	 * @return mixed
	 */
	public function invoke($method, array $args = array(), $wrap = 'request', $keys = false)
	{
		$parms   = array_merge($args, array('auth' => $this->auth));
		$request = $wrap ? array($wrap => $parms) : $parms;
		$request = $this->convertDataForService($request);

		$cache_id = serialize($request) . ($keys ? serialize($keys) : '');
		$cache_path = '/'. IPOLH_DPD_MODULE .'/api/'. $method;

		if ($this->cache()->initCache($this->cache_time, $cache_id, $cache_path)) {
			return $this->cache()->GetVars();
		}

		try {
			if ($this->initError) {
				throw new SystemException($this->initError);
			}

			$ret = $this->$method($request);

			// hack return binary data
			if ($ret 
				&& isset($ret->return->file)
			) {
				return array('FILE' => $ret->return->file);
			}

			$ret = json_encode($ret);
			$ret = json_decode($ret, true);
			$ret = $ret['return'];

			$ret = $this->convertDataFromService($ret, $keys);

			if ($this->cache()->startDataCache()) {
				$this->cache()->endDataCache($ret);
			}
		} catch (\Exception $e) {
			AddMessage2Log($e->getMessage());
			$ret = false;
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

	/**
	 * Конвертирует переданные данные в формат внешнего API
	 *
	 * Под конвертацией понимается:
	 * - перевод названий параметров в camelCase
	 * - смена кодировки при необходимости
	 * 
	 * @param  array $data 
	 * @return array
	 */
	protected function convertDataForService($data)
	{
		$ret = array();
		foreach ($data as $key => $value) {
			$key = Utils::underScoreToCamelCase($key);

			$ret[$key] = is_array($value) 
							? $this->convertDataForService($value)
							: ($this->convertEncoding ? Utils::convertEncoding($value, SITE_CHARSET, 'UTF-8') : $value);
		}

		return $ret;
	}

	protected function convertDataFromService($data, $keys = false)
	{
		$keys = $keys ? array_flip((array) $keys) : false;

		$ret = array();
		foreach ($data as $key => $value) {
			$key = $keys 
					? implode(':', array_intersect_key($value, $keys))
					: Utils::camelCaseToUnderScore($key);

			$ret[$key] = is_array($value)
							? $this->convertDataFromService($value)
							: ($this->convertEncoding ? Utils::convertEncoding($value, 'UTF-8', SITE_CHARSET) : $value);
		}

		return $ret;
	}

	// public function __doRequest($request, $location, $action, $version, $one_way = 0)
	// {
	// 	$ret = parent::__doRequest($request, $location, $action, $version, $one_way);

	// 	if (!is_dir(__DIR__ .'/logs/')) {
	// 		mkdir(__DIR__ .'/logs/', 0777);
	// 	}

	// 	file_put_contents(__DIR__ .'/logs/'. md5($location) .'.logs', ''
	// 		. 'LOCATION: '. PHP_EOL . $location . PHP_EOL
	// 		. 'REQUEST : '. PHP_EOL . $request  . PHP_EOL
	// 		. 'ANSWER  : '. PHP_EOL . $ret      . PHP_EOL
	// 	);

	// 	return $ret;	
	// }
}