<?php

namespace Bendersay\Exportimport;
use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;

/**
 * Содержит вспомогательные методы доступные везде
 *
 * @author bender_say
 */
class Helper {
	
	public static function UseModuleHL() {
		if (!\Bitrix\Main\Loader::includeModule('highloadblock')) {
			throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_HIGHLOADBLOCK'));
		}
	}
	
	/**
	 * Возвращает имена полей и их параметры HL
	 * @param type $ID
	 * @return array
	 */
	public static function GetUserEntity($ID) {
		self::UseModuleHL();
		$rows = [];
		$res = \CUserTypeEntity::GetList(
				array(), array(
				'ENTITY_ID' => 'HLBLOCK_' . $ID
				)
		);
		$connection = \Bitrix\Main\Application::getConnection();
		while ($row = $res->fetch()) {
			$langs = $connection->query('SELECT * FROM `b_user_field_lang` WHERE `USER_FIELD_ID` = ' . $row['ID'] . '');
			while ($lang = $langs->fetch()) {
				$row['langs'][$lang['LANGUAGE_ID']] = $lang;
			}
			$rows[$row['ID']] = $row;
		}
		return $rows;
	}
	
	/**
	 * Возвращает список HL
	 * @return array() 
	 */
	public static function GetAllHL() {
		self::UseModuleHL();
		// init data
		$hls = array();
		$res = HL\HighloadBlockTable::getList(array(
				'select' => array(
					'*', 'NAME_LANG' => 'LANG.NAME'
				),
				'order' => array(
					'NAME_LANG' => 'ASC', 'NAME' => 'ASC'
				)
		));
		while ($row = $res->fetch()) {
			$row['NAME_LANG'] = $row['NAME_LANG'] != '' ? $row['NAME_LANG'] : $row['NAME'];
			$hls[$row['ID']] = $row;
		}
		return $hls;
	}
	
	/**
	 * Возвращает размер в байтах
	 * @param type $val
	 * @return int
	 */
	public static function GetBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch ($last) {
			// Модификатор 'G' доступен, начиная с PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

}
