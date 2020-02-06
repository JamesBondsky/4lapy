<?php
namespace Ipolh\DPD\DB\Location;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

use \Ipolh\DPD\API\User as API;
use \Ipolh\DPD\DB\Location\Table as LocationTable;
use \Ipolh\DPD\Utils;

Loc::loadMessages(__FILE__);

class Agent
{
	protected static $cityFilePath = 'ftp://intergration:xYUX~7W98@ftp.dpd.ru:22/integration/GeographyDPD_20170618.csv';

	/**
	 * Обновляет список всех городов обслуживания
	 * 
	 * @return void
	 */
	public static function loadAll($position = 0)
	{
		self::$cityFilePath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/ipol.dpd/data/cities.txt';

		$file = fopen(self::$cityFilePath, 'r');
		if ($file === false) {
			return false;
		}

		fseek($file, $position ?: 0);
		$start_time = time();

		while(($row = fgetcsv($file, 0, ';')) !== false) {
			if (Utils::isNeedBreak($start_time)) {
				return ftell($file);
			}

			$row = \Ipolh\DPD\Utils::convertEncoding($row, 'windows-1251', SITE_CHARSET);

			$regionName = end(explode(',', $row[4]));
			$regionName = self::trim($regionName, 'IPOLH_DPD_REGION_NAME_TRIM_MULTI');
			$regionName = self::trim($regionName, 'IPOLH_DPD_REGION_NAME_TRIM');

			$cityName = $row[3];
			$cityName = self::trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM_MULTI');
			$cityName = self::trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM');

			$arCity = array(
				'COUNTRY_CODE' => mb_substr($row[1], 0, 2),
				'COUNTRY_NAME' => $row[5],
				'REGION_NAME'  => $regionName,
				'REGION_CODE'  => '',
				'CITY_ID'      => $row[0],
				'CITY_CODE'    => mb_substr($row[1], 2),
				'CITY_NAME'    => $cityName,
				'ABBREVIATION' => $row[2],
			);

			self::loadLocation($arCity);
		}

		return true;
	}

	/**
	 * Обновляет города в которых доступен НПП
	 * 
	 * @return void
	 */
	public static function loadCashPay($position = 'RU:0')
	{
		$position   = explode(':', $position ?: 'RU:0');
		$index      = 0;
		$started    = false;
		$start_time = time();

		foreach(['RU', 'KZ', 'BY', 'UA'] as $countryCode) {
			if ($position[0] != $countryCode && $started === false) {
				continue;
			}

			$started  = true;
			$arCities = API::getInstance()->getService('geography')->getCitiesCashPay($countryCode);

			foreach ($arCities as $arCity) {
				if ($index++ < $position[1]) {
					continue;
				}

				if (Utils::isNeedBreak($start_time)) {
					return sprintf('%s:%s', $countryCode, $index);
				}

				static::loadLocation($arCity, [
					'IS_CASH_PAY' => 'Y',
				]);
			}
		}

		return true;
	}

	/**
	 * Ищет соответствие с местоположением битрикса и загружает данные города в БД
	 * 
	 * @param  array $arCity
	 * @return bool
	 */
	protected static function loadLocation($arCity, $additional_fields = array())
	{
		$city = static::findCity($arCity);

		if (!$city) {
			return false;
		}

		$fields = array_merge([
			'COUNTRY_CODE' => $arCity['COUNTRY_CODE'],
			'COUNTRY_NAME' => $arCity['COUNTRY_NAME'],
			
			'REGION_CODE'  => $arCity['REGION_CODE'],
			'REGION_NAME'  => $arCity['REGION_NAME'],

			'CITY_ID'      => $arCity['CITY_ID'],
			'CITY_CODE'    => $arCity['CITY_CODE'],
			'CITY_NAME'    => $arCity['CITY_NAME'],

			'LOCATION_ID'  => $city['ID'],
		], $additional_fields);

		$exists = LocationTable::getList([
			'select' => ['ID'],
			'filter' => ['CITY_ID' => $arCity['CITY_ID']]
		])->fetch();

		if ($exists) {
			$result = LocationTable::update($exists['ID'], $fields);
		} else {
			$result = LocationTable::add($fields);
		}

		return $result->isSuccess() ? ($exists ? $exists['ID'] : $result->getId()) : false;
	}

	protected function fixAnalogs($arCity)
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

			if (in_array($arCity['REGION_NAME'], $FROM)
				&& $arData['FROM_CODE'] == $arCity['REGION_CODE']
			) {
				$arCity['REGION_CODE'] = $arData['TO_CODE'];
				$arCity['REGION_NAME'] = $arData['TO_CITY'];
			}
		}

		return $arCity;
	}

	/**
	 * Вырезает из строки указанные подстроки
	 * 
	 * @param  $string
	 * @param  $toRemove
	 * @return string
	 */
	protected static function trim($string, $toRemove)
	{
		$string      = \Ipolh\DPD\Utils::convertEncoding($string, SITE_CHARSET, 'UTF-8');
		$replacement = \Ipolh\DPD\Utils::convertEncoding(Loc::getMessage($toRemove), SITE_CHARSET, 'UTF-8');

		$replacement = explode(';', $replacement);
		$replacement = array_map('trim', $replacement);
		$replacement = array_map(function($text) {
			return '/\b'. preg_quote(trim($text)) .'\b/sUui';
		}, $replacement);

		$string = preg_replace($replacement, '', $string);
		$string = trim($string, '.');
		$string = trim(preg_replace('{\s{2,}}', ' ', $string));

		return \Ipolh\DPD\Utils::convertEncoding($string, 'UTF-8', SITE_CHARSET);
	}

	/**
	 * Поиск местоположения по фильтру
	 * 
	 * @param  array $filter
	 * @return array|bool
	 */
	protected static function findLocation($filter)
	{
		static $cache = array();
		static $items = false;

		$key = md5(serialize($filter));

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		if ($items === false) {
			$typeCountryId = Option::get(IPOLH_DPD_MODULE, "LOCATION_COUNTRY", 1);
			$typeRegionId  = Option::get(IPOLH_DPD_MODULE, "LOCATION_REGION",  3);
			$typeCityId    = Option::get(IPOLH_DPD_MODULE, "LOCATION_CITY",    5);
			$typeVillageId = Option::get(IPOLH_DPD_MODULE, "LOCATION_VILLAGE", 6);

			$rsItems = \Bitrix\Sale\Location\LocationTable::getList([
				'select' => [
					'ID',
					'LNAME'       => 'NAME.NAME',
					'SHORT_NAME'  => 'NAME.SHORT_NAME',
					'LANGUAGE_ID' => 'NAME.LANGUAGE_ID',
					'LEFT_MARGIN', 'RIGHT_MARGIN', 'TYPE_ID'
				],

				'filter' => [
					'TYPE_ID'          => [$typeCountryId, $typeRegionId, $typeCityId, $typeVillageId],
					'NAME.LANGUAGE_ID' => 'ru',
				],

				'order' => ['TYPE_ID' => 'ASC', 'NAME.NAME_UPPER' => 'ASC'],
			]);

			$items = array();
			while($item = $rsItems->fetch()) {
				$typeId   = $item['TYPE_ID'];
				$cityName = $item['LNAME'];

				if ($item['TYPE_ID'] == $typeRegionId) {
					$cityName = self::trim($cityName, 'IPOLH_DPD_REGION_NAME_TRIM_MULTI');
					$cityName = self::trim($cityName, 'IPOLH_DPD_REGION_NAME_TRIM');
				} elseif (in_array($item['TYPE_ID'], [$typeCityId, $typeVillageId])) {
					$cityName = self::trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM_MULTI');
					$cityName = self::trim($cityName, 'IPOLH_DPD_CITY_NAME_TRIM');
				}

				$cityName = mb_strtoupper($cityName, SITE_CHARSET);

				if (!isset($items[$typeId])) {
					$items[$typeId] = array();
				}

				if (!isset($items[$typeId][$cityName])) {
					$items[$typeId][$cityName] = array();
				}

				$items[$typeId][$cityName][] = $item;
			}

			unset($rsItems);
		}

		$toFind   = array();
		$typeIds  = (array) $filter['TYPE_ID'];
		$cityName = $filter['NAME_UPPER'];

		foreach ($typeIds as $typeId) {
			$toFind = array_merge($toFind, $items[$typeId][$cityName] ?: array());
		}

		unset($filter['TYPE_ID'], $filter['NAME_UPPER']);

		$ret = array_filter($toFind, function($item) use ($filter) {
			$all_count = sizeof($filter);

			array_walk($filter, function(&$expr, $field) use ($item) {
				$expr = self::checkExpr($item, $field, $expr); 
			});

			return sizeof(array_filter($filter)) == $all_count;
		});

		return $cache[$key] = $ret;
	}

	protected static function checkExpr($item, $field, $exprs)
	{
		$exprs   = (array) $exprs;
		$success = false;

		foreach ($exprs as $expr) {
			$cond  = substr($field, 0, 2);
			$value = $item[substr($field, 2)];

			if ($cond == '<=') {
				$success = $value <= $expr;
			} elseif ($cond == '>=') {
				$success = $value >= $expr;
			} elseif ($cond == '!=') {
				$success = $value != $expr;
			} else {
				$cond  = substr($fieldName, 0, 1);
				$value = $item[substr($field, 1)];

				if ($cond == '<') {
					$success = $value < $expr;
				} elseif ($cond == '>') {
					$success = $value > $expr;
				} elseif ($cond == '!') {
					$success = $value != $expr;
				} else {
					$value = $item[$field];
					
					if (strpos($expr, '%') !== false) {
						$expr    = preg_quote($expr);
						$expr    = str_replace(['%', '\\%'], '.*', $expr);
						$success = preg_match('{'. $expr .'}sUi', $value);
					} else {
						$success = $value == $expr; 
					}
				}
			}

			if ($success) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Поиск города
	 * 
	 * @param  array $arCity
	 * @return array|false
	 */
	protected static function findCity($arCity)
	{
		static $cache = array();

		$key = $arCity['CITY_ID'];

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$typeCountryId = Option::get(IPOLH_DPD_MODULE, "LOCATION_COUNTRY", 1);
		$typeRegionId  = Option::get(IPOLH_DPD_MODULE, "LOCATION_REGION",  3);
		$typeCityId    = Option::get(IPOLH_DPD_MODULE, "LOCATION_CITY",    5);
		$typeVillageId = Option::get(IPOLH_DPD_MODULE, "LOCATION_VILLAGE", 6);

		$arCity = static::fixAnalogs($arCity);

		$country = reset(static::findLocation([
			'TYPE_ID'     => $typeCountryId,
			'NAME_UPPER'  => mb_strtoupper($arCity['COUNTRY_NAME'], SITE_CHARSET),
		]));

		if (!$country) {
			return $cache[$key] = false;
		}

		$region = reset(static::findLocation([
			'TYPE_ID'          => [$typeRegionId, $typeCityId],
			'NAME_UPPER'       => mb_strtoupper($arCity['REGION_NAME'], SITE_CHARSET),
			'>=LEFT_MARGIN'    => $country['LEFT_MARGIN'],
			'<=RIGHT_MARGIN'   => $country['RIGHT_MARGIN'],
		]));

		if ($region 
			&& $arCity['REGION_NAME'] == $arCity['CITY_NAME']
			&& $region['TYPE_ID'] == $typeCityId
		) {
			// если имя региона совпадает с именем города
			// и мы нашли город, прекращаем поиски
			return $cache[$key] = $region;
		}
		
		// т.к названия нас. пунктов могут совпадать в одном регионе
		// выбираем тип искомой записи.
		$cityAbbr = explode(',', Loc::getMessage('IPOLH_DPD_CITY_ABBR'));
		$findCityType = in_array($arCity['ABBREVIATION'], $cityAbbr) ? $typeCityId : $typeVillageId;
		
		// и ищем в стране
		$cities = static::findLocation($f = [
			'TYPE_ID'          => $findCityType,
			'NAME_UPPER'       => mb_strtoupper($arCity['CITY_NAME'], SITE_CHARSET),
			'>=LEFT_MARGIN'    => $region['LEFT_MARGIN'],
			'<=RIGHT_MARGIN'   => $region['RIGHT_MARGIN'],
		]);

		// если нашли единственный нас. пункт и он город, возвращаем его
		if (sizeof($cities) == 1) {
			return $cache[$key] = reset($cities);
		}

		// // иначе сверяем его регион
		// if ($region) {
		// 	foreach ($cities as $city) {
		// 		if ($city['LEFT_MARGIN'] >= $region['LEFT_MARGIN']
		// 			&& $city['RIGHT_MARGIN'] <= $region['RIGHT_MARGIN']
		// 		) {
		// 			return $cache[$key] = $city;
		// 		}
		// 	}
		// }
		
		return $cache[$key] = false;
	}
}