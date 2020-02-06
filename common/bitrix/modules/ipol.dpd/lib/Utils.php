<?php
namespace Ipolh\DPD;

class Utils
{
	/**
	 * Переводит строку из under_score в camelCase
	 * 
	 * @param  string  $string                   строка для преобразования
	 * @param  boolean $capitalizeFirstCharacter первый символ строчный или прописной
	 * @return string
	 */
	public static function underScoreToCamelCase($string, $capitalizeFirstCharacter = false)
	{
		// символы разного регистра
		if (/*strtolower($string) != $string
			&&*/ strtoupper($string) != $string
		) {
			return $string;
		}

		$string = strtolower($string);
		$string = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

		if (!$capitalizeFirstCharacter) {
			$string[0] = strtolower($string[0]);
		}

		return $string;
	}

	/**
	 * Переводит строку из camelCase в under_score
	 * 
	 * @param  string  $string    строка для преобразования
	 * @param  boolean $uppercase
	 * @return string
	 */
	public static function camelCaseToUnderScore($string, $uppercase = true)
	{
		// символы разного регистра
		if (strtolower($string) != $string
			&& strtoupper($string) != $string
		) {
			$string = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');;
		}		

		if ($uppercase) {
			$string = strtoupper($string);
		}

		return $string;
	}

	/**
	 * Конверирует кодировку
	 * В качестве значений может быть как скалярный тип, так и массив
	 *
	 * @param mixed $data
	 * @param string $fromEncoding
	 * @param string $toEncoding
	 */
	public static function convertEncoding($data, $fromEncoding, $toEncoding)
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = static::convertEncoding($value, $fromEncoding, $toEncoding);
			}
		} else {
			$data = iconv($fromEncoding, $toEncoding, $data);
		}

		return $data;
	}

	/**
	 * Возвращает свойства заказа для типа платильщика
	 * 
	 * @param  int $personeTypeId
	 * @return array
	 */
	public static function getOrderProps($personeTypeId)
	{
		$rsProps = \CSaleOrderProps::GetList(
			$arOrder  = array('SORT' => 'ASC'),
			$arFilter = array('PERSON_TYPE_ID' => $personeTypeId)
		);

		$result = array();
		while($arProp = $rsProps->Fetch()) {
			$result[] = $arProp;
		}

		return $result;
	}

	/**
	 * Возвращает значение св-в заказа
	 * 
	 * @param  int $orderId
	 * @param  int $personeTypeId
	 * @return array
	 */
	public static function getOrderPropsValue($orderId, $personeTypeId = false)
	{}

	/**
	 * Конвертирует дату из bitrix формата в DPD
	 * 
	 * @param string $date
	 * @return string
	 */
	public static function DateBitrixToDpd($date)
	{
		return ConvertDateTime($date, "YYYY-MM-DD", "ru");
	}
	
	/**
	 * Конвертирует дату из DPD формата в bitrix
	 * 
	 * @param string $date
	 * @return string
	 */
	public static function DateDpdToBitrix($date)
	{
		return ConvertDateTime($date, "DD.MM.YYYY", "ru");
	}

	/**
	 * Возвращает местоположение магазина
	 * 
	 * @return int
	 */
	public static function getSaleLocationId()
	{
		$defaultLocation = \Bitrix\Main\Config\Option::get('sale', 'location', '', ADMIN_SECTION ? 's1' : false);
		$currentLocation = \Bitrix\Main\Config\Option::get(IPOLH_DPD_MODULE, 'SENDER_LOCATION', $defaultLocation);

		$arBxLocation = \CSaleLocation::GetByID($currentLocation, "ru");
		if (!$arBxLocation) {
			return false;
		}

		return $arBxLocation['ID'];
	}

	public static function isNeedBreak($start_time)
	{
		$max_time = (ini_get('max_execution_time') ?: 60);
		$max_time = max(min($max_time, 60), 10);

		return time() >= ($start_time + $max_time - 5);
	}
}