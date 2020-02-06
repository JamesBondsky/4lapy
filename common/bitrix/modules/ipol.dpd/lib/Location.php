<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Data\Cache;

use \Ipolh\DPD\API\User;

Loc::loadMessages(__FILE__);

class Location
{
	protected static $instance;

	protected $api;

	protected $cache;

	protected $cache_time = IPOLH_DPD_CACHE_TIME;

	protected $typeRegionId;

	protected $typeCityId;

	protected $typeVillageId;

	public static function getInstance()
	{
		return self::$instance ?: self::$instance = new self(User::getInstance());
	}

	public function __construct(User $api)
	{
		$this->api = $api;

		$this->typeRegionId  = Option::get(IPOLH_DPD_MODULE, "LOCATION_REGION",  3);
		$this->typeCityId    = Option::get(IPOLH_DPD_MODULE, "LOCATION_CITY",    5);
		$this->typeVillageId = Option::get(IPOLH_DPD_MODULE, "LOCATION_VILLAGE", 6);
	}

	/**
	 * Возвращает полное представление местоположения по его ID
	 * в соответствии с DPD
	 * 
	 * @param  $locationId id местоположения
	 * @return array
	 */
	public function find($locationId)
	{
		$cache_id = $locationId;
		$cache_path = '/'. IPOLH_DPD_MODULE .'/location/';

		if ($this->cache()->initCache($this->cache_time, $cache_id, $cache_path)) {
			return $this->cache()->GetVars();
		}

		$arBxLocation = \CSaleLocation::GetByID($locationId, "ru");
		if (!$arBxLocation) {
			return false;
		}

		$arLocation = $this->normalizeLocation($arBxLocation['ID']);
		if (!$arLocation) {
			return false;
		}
		
		$arRegion = $this->findRegion($arLocation['REGION_NAME'], $arLocation['CITY_NAME']);
		$arCity   = $this->findCity($arLocation['CITY_NAME'], $arRegion['CODE']);

		$ret = array_filter(array(
			'ID'           => $arLocation['ID'],
			'COUNTRY_NAME' => $arLocation['COUNTRY_NAME'],

			'REGION_NAME'  => $arRegion['NAME'],
			'REGION_CODE'  => $arRegion['CODE'],
			
			'CITY_NAME'    => $arCity['NAME'],
			'CITY_ID'      => $arCity['ID'],
			'CITY_CODE'    => $arCity['CODE'],
			
			'ZIP'          => $arLocation['ZIP'],
		));

		if ($this->cache()->startDataCache()) {
			$this->cache()->endDataCache($ret);
		}

		return $ret;
	}

	/**
	 * Возвращает нормализованное представление местоположения
	 * опираясь на соответствие типов местоположений из настроек
	 * 
	 * @param  $LOCATION_ID
	 * @return array
	 */
	public function normalizeLocation($LOCATION_ID)
	{
		$COUNTRY_NAME = "";
		$REGION_NAME  = "";
		$CITY_NAME    = "";
		$VILLAGE_NAME = "";

		$arLocations = array();
		$rsLocations = \Bitrix\Sale\Location\LocationTable::getPathToNode($LOCATION_ID, array(
			'select' => array(
				'LNAME' => 'NAME.NAME',
				'SHORT_NAME' => 'NAME.SHORT_NAME',
				'LEFT_MARGIN', 'RIGHT_MARGIN', 'ID', 'CODE', 'DEPTH_LEVEL', 'PARENT_ID',
				'TYPE_ID', 'COUNTRY_ID', 'REGION_ID', 'CITY_ID',
			),

			'filter' => array(
				'NAME.LANGUAGE_ID' => LANGUAGE_ID
			)
		));

		$rsLocations->addReplacedAliases(array('LNAME' => 'NAME'));
		while($arItem = $rsLocations->Fetch()) {
			if (!$COUNTRY_NAME) {
				$COUNTRY_NAME = $arItem["NAME"];
			}

			$DEF_CITY_NAME = $arItem["NAME"];
			$arLocations[] = $arItem;
		}

		if (empty($arLocations)) {
			return false;
		}

		foreach($arLocations as $arLocation) {
			if ($arLocation['TYPE_ID'] == $this->typeRegionId) {
				$REGION_NAME = $arLocation['NAME'];
			} elseif ($arLocation['TYPE_ID'] == $this->typeCityId) {
				$CITY_NAME = $arLocation['NAME'];
			} elseif ($arLocation['TYPE_ID'] == $this->typeVillageId) {
				$VILLAGE_NAME = $arLocation['NAME'];
			}
		}

		$VILLAGE_NAME = $VILLAGE_NAME ?: $DEF_CITY_NAME;
		$CITY_NAME    = $CITY_NAME    ?: $VILLAGE_NAME;
		$REGION_NAME  = $REGION_NAME  ?: $CITY_NAME;
		$ZIP          = 0;

		$rsZip = \CSaleLocation::GetLocationZIP($LOCATION_ID);
		if ($arZip = $rsZip->Fetch()) {
			$ZIP = $arZip['ZIP'];
		}

		return array(
			'ID'           => $LOCATION_ID,
			'COUNTRY_NAME' => $COUNTRY_NAME,
			'REGION_NAME'  => $REGION_NAME,
			'CITY_NAME'    => $CITY_NAME,
			'ZIP'          => $ZIP,
		);
	}

	/**
	 * Поиск региона по его названию
	 * возвращается имя региона и его код
	 * 
	 * @return array
	 */
	public function findRegion($regionName, $cityName = '')
	{
		$time = getmicrotime();

		$regionName = $this->str_trim($regionName, 'IPOLH_DPD_REGION_NAME_TRIM_MULTI');
		$regionName = $this->str_trim($regionName, 'IPOLH_DPD_REGION_NAME_TRIM');
		$regionCode = '';

		// TODO: перенести в БД или искать по кладру
		$arRegions = require($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/'. IPOLH_DPD_MODULE .'/data/region_code.php');
		foreach($arRegions as $arRegion) {
			$name = iconv('windows-1251', SITE_CHARSET, $arRegion['NAME']);

			if ($name == $regionName) {
				$regionCode = $arRegion['CODE'];
				break;
			}
		}

		return $this->fixRegionAnalogs($cityName, $regionCode);
	}

	/**
	 * Поиск города по его названию с учетом
	 * доставок DPD
	 *
	 * return array
	 */
	public function findCity($cityName, $regionCode, $country = 'RU')
	{
		$arResult = array('NAME' => $cityName, 'ID' => '', 'CODE' => '');

		if (!$regionCode) {
			return $arResult;
		}
		
		$cityName = $this->str_trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM_MULTI');
		$cityName = $this->str_trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM');
		$arCities = $this->getAll();

		if (!$arCities) {
			return $arResult;
		}

		$cityName = mb_strtolower($cityName, SITE_CHARSET);
		foreach($arCities as $arCity) {
			$name = mb_strtolower($arCity['CITY_NAME'], SITE_CHARSET);
			$name = trim(preg_replace('/^(.+)\s\(.+\)$/', '$1', $name));

			if ($arCity['COUNTRY_CODE'] == $country
				&& $arCity['REGION_CODE'] == $regionCode
				&& $name == $cityName
			) {
				$arResult['ID']   = $arCity['CITY_ID'];
				$arResult['CODE'] = $arCity['CITY_CODE'];
			}
		}

		return $arResult;
	}

	/**
	 * Возвращает список населенных пунктов в которых доступна
	 * доставка DPD
	 * 
	 * @return array
	 */
	public function getAll()
	{
		return $this->api->getService('geography')->getCitiesCashPay();
	}

	/**
	 * Функция вырезает из строки географические обозначения
	 * 
	 * @param  string $string   строка для обработки
	 * @param  string $toRemove ключ в языковом файле фразы для удаления
	 * @return string
	 */
	private function str_trim($string, $toRemove)
	{
		$replacement = explode(';', Loc::getMessage($toRemove));
		$replacement = array_map('trim', $replacement);

		$string  = str_replace($replacement, '', $string);

		return trim(preg_replace('{\s{2,}}', ' ', $string));
	}

	/**
	 * Приводит данные региона к более общему региону
	 *
	 * Например города Москва;Зеленоград;Tвepь;Tверь_969;Московский
	 * объединяются в один регион Москва
	 * 
	 * @param  string $regionName
	 * @param  string $regionCode
	 * @return array
	 */
	protected function fixRegionAnalogs($regionName, $regionCode)
	{
		$arReplacement = array(
			array(
				// Города аналоги
				'FROM_CITY' => Loc::getMessage('IPOLH_DPD_CITY_NAME_MOSCOW_CITYES'),
				// Регион аналогов
				'FROM_CODE' => 50,

				// На что заменяем
				'TO_CITY'   => Loc::getMessage('IPOLH_DPD_CITY_NAME_MOSCOW'),
				// Регион на который меняем
				'TO_CODE'   => 77,
			),

			array(
				'FROM_CITY' => Loc::getMessage('IPOLH_DPD_CITY_NAME_PITER_CITYES'),
				'FROM_CODE' => 47,

				'TO_CITY'   => Loc::getMessage('IPOLH_DPD_CITY_NAME_PITER'),
				'TO_CODE'   => 78,
			),

			array(
				'FROM_CITY' => Loc::getMessage('IPOLH_DPD_CITY_NAME_SEVASTOPOL_CITYES'),
				'FROM_CODE' => 91,

				'TO_CITY'   => Loc::getMessage('IPOLH_DPD_CITY_NAME_SEVASTOPOL'),
				'TO_CODE'   => 92,
			),
		);

		foreach ($arReplacement as $arData) {
			$FROM = array_map('trim', explode(';', $arData['FROM_CITY']));

			if (in_array($regionName, $FROM)
				&& $arData['FROM_CODE'] == $regionCode
			) {
				// $regionName = $arData['TO_CITY'];
				$regionCode = $arData['TO_CODE'];
			}
		}

		return array('NAME' => $regionName, 'CODE' => $regionCode);
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