<?php

namespace Bendersay\Exportimport;
use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Localization\Loc;

/**
 * Description of importjson
 *
 * @author bender_say
 */
class ImportJSON extends Import {
	
	public function ImportDataJSON(array $param) {
		
		$hldata = HL\HighloadBlockTable::getById($param['hl_id'])->fetch();
		$entity = HL\HighloadBlockTable::compileEntity($hldata);
		$ob_hldata = $entity->getDataClass();
		$res = [];
		
		if (!empty($param['data']['items'])) {
			foreach ($param['data']['items'] as $key => $item) {

				// пропускаем уже обновленные
				if ($key <= $param['arr_step']['export_step_id'] && $param['arr_step']['export_step_id'] > 0) {
					continue;
				}
				
				$prep_arr = $this->DataPreparation($item, $param);
				// Есть ошибки, запишим
				if (!empty($prep_arr['error'])) {
					$res['error'][$key]['text_error'] = str_replace(
						['#key#', '#prop#'],
						[$key, implode(', ', $prep_arr['error'])], 
						Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_IMPORT_FILE_FIELD'));
					$res['error'][$key]['item'] = $item;
				}
				
				// Если нет ключа добавляем запись, иначе обновим
				if (empty($param['import_key'])) {
					$result = $ob_hldata::add($prep_arr['item']);
				} else {
					// Пытаемся найти запись
					$row = $ob_hldata::getRow(array(
						'select' => array('ID'),
						'filter' => array('=' . $param['import_key'] => (int)$prep_arr['item'][$param['import_key']])
					));
					if ($row) {
						$result = $ob_hldata::update($row['ID'], $prep_arr['item']);
					} else {
						$result = $ob_hldata::add($prep_arr['item']);
					}
				}
				// Запись результатов
				if (!$result->isSuccess()) {
					$res['error'][$key]['text_error'] = $result->getErrorMessages();
					$res['error'][$key]['item'] = $prep_arr['item'];
				}
				
				// Если прошли 1 шаг прерываем и записываем текущий шаг
				$res['fields_count'] = $key + 1;
				if ($key == ($param['arr_step']['export_step_id'] + $param['export_count_row'])) {
					$res['step_id'] = $key;
					break;
				}
				
			}
		}
		return $res;
	}
	
	/**
	 * Смена кодировки для JSON
	 * @param type $item
	 * @param type $key
	 */
	public function UTF8Cp1251(&$item, $key) {
		if(is_string($item)) {
			$item = iconv('UTF-8', LANG_CHARSET . "//IGNORE", $item);
		}
	}
	
	protected function DataPreparation(array $item, array $param) {
		
		// Перекодируем из JSON
		array_walk_recursive($item,  array(&$this, 'UTF8Cp1251'));

		$error = [];
		foreach ($item as $k_item => $v_item) {
			// Обработка файла
			if (!empty($item[$k_item]) && $param['arr_step']['FIELDS_TYPE'][$k_item] == 'file') {
				if (is_array($item[$k_item])) {
					$vr_arr = [];
					foreach ($item[$k_item] as $v_file) {
						$vr_mak = \CFile::MakeFileArray($v_file);
						if ($vr_mak == NULL) {
							$error[] = $v_file;
						} else {
							$vr_arr[] = $vr_mak;
						}
					}
					$item[$k_item] = $vr_arr;
				} else {
					$item[$k_item] = \CFile::MakeFileArray($v_item);
					if ($item[$k_item] == NULL) {
						$error[] = $v_item;
					}
				}
			}
		}
		// добавляем ID, если есть ключ для обновления
		if (!empty($param['import_key'])) {
			$param['arr_step']['FIELDS']['ID'] = $param['import_key'];
		}
		
		// Возвращаем нужные поля
		$new_item = [];
		foreach ($param['arr_step']['FIELDS'] as $key => $field) {
			$new_item[$key] = $item[$field];
		}
		
		return ['item' => $new_item, 'error' => $error];
	}
	
	
}
