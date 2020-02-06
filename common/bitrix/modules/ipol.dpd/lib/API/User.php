<?php
namespace Ipolh\DPD\API;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;

class User
{
	protected static $instance = array();

	protected static $classmap = array(
		'geography'   => '\\Ipolh\\DPD\\API\\Service\\Geography',
		'calculator'  => '\\Ipolh\\DPD\\API\\Service\\Calculator',
		'order'       => '\\Ipolh\\DPD\\API\\Service\\Order',
		'label-print' => '\\Ipolh\\DPD\\API\\Service\\LabelPrint',
		'tracking'    => '\\Ipolh\\DPD\\API\\Service\\Tracking',
	);

	/**
	 * Возвращает инстанс класса с параметрами доступа указанными в настройках
	 * 
	 * @return self
	 */
	public static function getInstance($defaultAccount = false)
	{
		$defaultAccount = $defaultAccount ?: Option::get(IPOLH_DPD_MODULE, 'API_DEF_COUNTRY');
		$defaultAccount = $defaultAccount == 'RU' ? '' : $defaultAccount;
		
		$clientNumber   = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_NUMBER_'. $defaultAccount, '_'));
		$clientKey      = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_KEY_'. $defaultAccount, '_'));
		$testMode       = Option::get(IPOLH_DPD_MODULE, 'IS_TEST');
		$currency       = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_CURRENCY_'. $defaultAccount, '_'), 'RUB');

		return self::$instance[$defaultAccount] ?: self::$instance[$defaultAccount] = new self(
			$clientNumber,
			$clientKey,
			$testMode,
			$currency
		);
	}

	/**
	 * Проверяет наличие данных авторизации для языка
	 * 
	 * @param  $accountLang
	 * @return boolean
	 */
	public static function isActiveAccount($defaultAccount = false)
	{
		$accountLang = $defaultAccount ?: Option::get(IPOLH_DPD_MODULE, 'API_DEF_COUNTRY');
		$accountLang = $accountLang == 'RU' ? '' : $accountLang;

		$clientNumber   = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_NUMBER_'. $accountLang, '_'));
		$clientKey      = Option::get(IPOLH_DPD_MODULE, trim('KLIENT_KEY_'. $accountLang, '_'));

		return $clientNumber && $clientKey;
	}

	protected $clientNumber;
	protected $secretKey;
	protected $testMode;
	protected $currency;

	public function __construct($clientNumber, $secretKey, $testMode = false, $currency = false)
	{
		$this->clientNumber = $clientNumber;
		$this->secretKey = $secretKey;
		$this->testMode = (bool) $testMode;
		$this->currency = $currency;
	}

	/**
	 * Возвращает номер клиента DPD
	 * 
	 * @return mixed
	 */
	public function getClientNumber()
	{
		return $this->clientNumber;
	}

	/**
	 * Возвращает токен авторизации DPD
	 * 
	 * @return mixed
	 */
	public function getSecretKey()
	{
		return $this->secretKey;
	}

	/**
	 * Проверяет включен ли режим тестирования
	 * 
	 * @return boolean
	 */
	public function isTestMode()
	{
		return (bool) $this->testMode;
	}

	/**
	 * Возвращает валюту аккаунта
	 * 
	 * @return string
	 */
	public function getClientCurrency()
	{
		return $this->currency;
	}

	/**
	 * Возвращает службу для доступа к API
	 * 
	 * @param  string $serviceName
	 * @return \Ipolh\API\Service\ServiceInterface
	 */
	public function getService($serviceName)
	{
		if (isset(self::$classmap[$serviceName])) {
			return $this->services[$serviceName] ?: $this->services[$serviceName] = new self::$classmap[$serviceName]($this);
		}

		throw new SystemException("Service {$serviceName} not found");
	}

	public function resolveWsdl($uri)
	{
		if ($this->testMode) {
			return str_replace('ws.dpd.ru', 'wstest.dpd.ru', $uri);
		}

		return $uri;
	}
}