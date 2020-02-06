<?php
namespace Ipolh\DPD\DB\Terminal;

use \Bitrix\Main;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Table extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_ipol_dpd_terminal';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),

			new Entity\StringField('LOCATION_ID', [
				'required' => false,
			]),

			new Entity\StringField('CODE', [
				'required' => false,
			]),

			new Entity\StringField('NAME', [
				'required' => false,
			]),

			new Entity\StringField('ADDRESS_FULL', [
				'required' => false,
			]),

			new Entity\StringField('ADDRESS_SHORT', [
				'required' => false,
			]),

			new Entity\StringField('ADDRESS_DESCR', [
				'required' => false,
			]),

			new Entity\StringField('PARCEL_SHOP_TYPE', [
				'required' => false,
			]),

			new Entity\StringField('SCHEDULE_SELF_PICKUP', [
				'required' => false,
			]),

			new Entity\StringField('SCHEDULE_SELF_DELIVERY', [
				'required' => false,
			]),

			new Entity\StringField('SCHEDULE_PAYMENT_CASH', [
				'required' => false,
			]),
			
			new Entity\StringField('SCHEDULE_PAYMENT_CASHLESS', [
				'required' => false,
			]),

			new Entity\FloatField('LATITUDE', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LONGITUDE', [
				'default_value' => 0,
			]),

			new Entity\BooleanField('IS_LIMITED', [
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			]),

			new Entity\FloatField('LIMIT_MAX_SHIPMENT_WEIGHT', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_MAX_WEIGHT', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_MAX_LENGTH', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_MAX_WIDTH', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_MAX_HEIGHT', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_MAX_VOLUME', [
				'default_value' => 0,
			]),

			new Entity\FloatField('LIMIT_SUM_DIMENSION', [
				'default_value' => 0,
			]),

			new Entity\FloatField('NPP_AMOUNT', [
				'default_value' => 0,
			]),

			new Entity\BooleanField('NPP_AVAILABLE', [
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			]),
			
			new Entity\BooleanField('UPDATE_CHECKED', [
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
	public static function getByLocationId($locationId, $select = array())
	{	
		return static::getList(array_filter([
			'select' => $select ?: null,
			'filter' => ['LOCATION_ID' => $arBxLocation['ID']]
		]));
	}

	public static function getByCode($code, $select = array())
	{
		return static::getList(array_filter([
			'select' => $select ?: null,
			'filter' => ['CODE' => $code]
		]))->fetch();
	}
}