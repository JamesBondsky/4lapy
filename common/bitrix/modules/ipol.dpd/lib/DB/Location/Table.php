<?php
namespace Ipolh\DPD\DB\Location;

use \Bitrix\Main;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Table extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_ipol_dpd_location';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),

			new Entity\StringField('COUNTRY_CODE', [
				'required' => false,
			]),

			new Entity\StringField('COUNTRY_NAME', [
				'required' => false,
			]),

			new Entity\StringField('REGION_CODE', [
				'required' => false,
			]),

			new Entity\StringField('REGION_NAME', [
				'required' => false,
			]),

			new Entity\StringField('CITY_ID', [
				'required' => false,
			]),

			new Entity\StringField('CITY_CODE', [
				'required' => false,
			]),

			new Entity\StringField('CITY_NAME', [
				'required' => false,
			]),

			new Entity\StringField('LOCATION_ID', [
				'required' => false,
			]),

			new Entity\BooleanField('IS_CASH_PAY', [
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			]),
		];
	}

	/**
	 * Возвращает запись по местоположению битрикса
	 * 
	 * @param  int $locationId
	 * @param  array  $select
	 * @return array|false
	 */
	public static function getByLocationId($locationId)
	{
		$arBxLocation = \CSaleLocation::GetByID($locationId, "ru");
		if (!$arBxLocation) {
			return false;
		}

		$ret = static::getList(array_filter([
			'filter' => ['LOCATION_ID' => $arBxLocation['ID']]
		]));

		$ret->addReplacedAliases(['LOCATION_ID' => 'ID']);

		return $ret->fetch();
	}

	/**
	 * Возвращает запись по ID города
	 * 
	 * @param  int $locationId
	 * @param  array  $select
	 * @return array|false
	 */
	public static function getByCityId($cityId, array $select = array())
	{
		return static::getList(array_filter([
			'select' => $select ?: null,
			'filter' => ['CITY_ID' => $cityId]
		]))->fetch();
	}
}