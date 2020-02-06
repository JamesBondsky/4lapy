<?php

namespace Bendersay\Exportimport;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;

/**
 * Description of exportjson
 *
 * @author bender_say
 */
class Exportjson extends Export {
	
	/**
	 * Запись в JSON файл
	 * @param array $param
	 * @return boolean
	 * @throws SystemException
	 */
	public function SaveJSON(array $param) {

		$mode =  ((int)$param['data']['step_id'] == 0) ? 'w' : 'a';
		
		$data = self::DataPreparation($param);
		
		$filename = \Bitrix\Main\Application::getDocumentRoot() . $param['url_data_file'];
		// Сохраняем название файла с настройками сервера
		$filename = iconv(LANG_CHARSET, mb_internal_encoding() . "//IGNORE", $filename);
		
		if ($mode == 'w') {
			$data_json = json_encode($data, JSON_FORCE_OBJECT);
		} elseif ($mode == 'a') {
			$this->ProvFileJSON($filename, $data['items']);	// Проверим перед записью
			$vr_arr = json_decode(file_get_contents($filename), true);
			$vr_arr['items'] = array_merge((array)$vr_arr['items'], (array)$data['items']);
			$vr_arr['items_all_count'] = $data['items_all_count'];
			$data_json = json_encode($vr_arr, JSON_FORCE_OBJECT);
		}

		file_put_contents($filename, $data_json);
		
		// Проверка записи
		if (file_exists($filename)) {
			return true;
		} else {
			throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE'));
		}
		
	}
	
	
	/**
	 * Готовит данные для записи
	 * @param array $param
	 * @return array
	 */
	protected function DataPreparation(array $param) {
		
		//print_r($param['data']['fields']);

		$data = [];
		if ($param['type'] == 'export_hl') {
			
			$data['hiblock'] = $param['data']['hiblock'];
			$data['langs'] = $param['data']['langs'];
			$data['items'] = $param['data']['fields'];

		} elseif ($param['type'] == 'export_data') {
			// Заголовки полей, только на первом шаге
			if ((int)$param['data']['step_id'] == 0) {
				$field = [];
				if (count($param['data']['fields']) > 0 ) {
					$field = current($param['data']['fields']);
				}
				$data['fields_name'] = array_keys($field);
			}
			// Значения полей
			$data['items_all_count'] = $param['data']['fields_all_count'];
			$data['items'] = self::GetObjdateToStrdate($param['data']['fields']);

		}
		
		// Перекодируем для JSON
		array_walk_recursive($data,  array(&$this, 'Cp1251UTF8'));

		return $data;
	}
	
	/**
	 * Смена кодировки для JSON
	 * @param type $item
	 * @param type $key
	 */
	public function Cp1251UTF8(&$item, $key) {
		if(is_string($item)) {
			$item = iconv(LANG_CHARSET, mb_internal_encoding() . "//IGNORE", $item);
		}
	}
	
	/**
	 * Перебирвет массив полей и возвращает строку даты для полей даты
	 * @param array $array
	 * @return array
	 */
	public function GetObjdateToStrdate(array $array=[]) {
		$items = [];
		foreach ($array as $key => $item) {
			$items[$key] = $item;
			foreach ($item as $k_field => $v_field) {
				if (is_object($v_field)) {
					if (get_class($v_field) == ('Bitrix\Main\Type\DateTime' || 'Bitrix\Main\Type\Date')) {
						$items[$key][$k_field] = $v_field->toString();
						continue;
					}
				}
			}
		}
		return $items;
	}
	
	/**
	 * Проверяем размер файла и размер доступной оперативы
	 * @param type $filename
	 * @throws SystemException
	 */
	protected  function ProvFileJSON($filename, $arr) {
		
		// Размер массива
		$m = memory_get_usage();
		$m_z = [] + $arr;
		$m = memory_get_usage() - $m;
		unset($m_z);
		
		$memory_limit = \Bendersay\Exportimport\Helper::GetBytes(ini_get('memory_limit'));
		$file_size = filesize($filename);
		
		if ((memory_get_usage() + $file_size * 7 + $m) > $memory_limit) {
			throw new SystemException(str_replace(['#file_size#', '#memory_limit#'],
				[round($file_size/1024/1024, 1, PHP_ROUND_HALF_UP), ini_get('memory_limit')],
				GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SIZE_EXP')));
		}
	}
}
