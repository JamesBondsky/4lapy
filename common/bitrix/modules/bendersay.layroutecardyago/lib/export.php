<?php

namespace Bendersay\Exportimport;
use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

/**
 * Description of export
 *
 * @author bender_say
 */
class Export {
	
	public function __construct() {
		Loc::loadMessages(__FILE__); 
		if (!\Bitrix\Main\Loader::includeModule('highloadblock')) {
			throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_HIGHLOADBLOCK'));
		}
	}
	
	/**
	 * Возвращает структуру HL
	 * @param int $ID HL
	 * @return array
	 * @throws SystemException
	 */
	public function GetHlStructure($ID) {
		$result = [];
		// Сам highloadblock
		$row = HL\HighloadBlockTable::getRow(array(
				'filter' => ['ID' => $ID]
		));
		if ($row) {
			$result['hiblock'] = $row;
			// Языки
			$res = HL\HighloadBlockLangTable::getList(array(
				'filter' => ['ID' => $ID]
			));
			while ($row = $res->fetch()) {
				$result['langs'][$row['LID']] = $row['NAME'];
			}
			// Записываем поля
			$result['fields'] = \Bendersay\Exportimport\Helper::GetUserEntity($ID);
		} else {
			throw new SystemException(str_replace('#ID#', $ID, Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_NOT_ID')));
		}

		return $result;
	}
	
	/**
	 * Возвращает структуру HL
	 * Проверка на int работает начиная с PHP 7.0.0
	 * @param int $ID
	 * @param array $arr_step
	 * @param array $select
	 * @return type
	 * @throws SystemException
	 */
	public function GetHlData($ID, array $arr_step, array $select = []) {
		$result = [];
		// Сам highloadblock
		$row = HL\HighloadBlockTable::getRow(array(
				'filter' => ['ID' => $ID]
		));
		if ($row) {
			$hldata = HL\HighloadBlockTable::getById($ID)->fetch();
			$entity = HL\HighloadBlockTable::compileEntity($hldata);
			$ob_hldata = $entity->getDataClass();
			
			// Общее число строк в таблице
			$count = $ob_hldata::getList(array(
				'select' => array('CNT'),
				'runtime' => array(
					new Entity\ExpressionField('CNT', 'COUNT(*)')
				)
			))->fetch();
			$result['fields_all_count'] = $count['CNT'];
			//$connection = \Bitrix\Main\Application::getConnection();
			//$tracker = $connection->startTracker();
			// Сама выборка
			array_unshift($select, 'ID');
			$result['fields'] = $ob_hldata::getList([
				'select' => $select,
				'order' => ['ID'],
				'filter' => ['>ID' => $arr_step['step_id']],
				'limit' => $arr_step['limit']
				])->fetchAll();
			
			// Узнаем последний ID
			$end_row = end($result['fields']); 
			$result['step_id'] = $end_row['ID'];
			
			// экспортировали строк
			$result['fields_count'] = $ob_hldata::getList(array(
				'select' => array('CNT'),
				'filter' => ['<=ID' => [$end_row['ID']]],
				'runtime' => array(
					new Entity\ExpressionField('CNT', 'COUNT(*)')
				)
			))->fetch()['CNT'];			

			//$connection->stopTracker();
			
		} else {
			throw new SystemException(str_replace('#ID#', $ID, Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_NOT_ID')));
		}

		return $result;
	}
	
}
