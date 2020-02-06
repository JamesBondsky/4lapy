<?php

/**
 * Description of ExportCSV
 *
 * @author bender_say
 */

namespace Bendersay\Exportimport;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;

class ExportCSV extends Export {
	
	/**
	 * Записывает CSV
	 * @param array $param
	 * @return boolean
	 * @throws SystemException
	 */
	public function SaveCSV(array $param) {
		
		$mode =  ((int)$param['data']['step_id'] == 0) ? 'w' : 'a';
		
		$list = self::DataPreparation($param);
		
		$filename = \Bitrix\Main\Application::getDocumentRoot() . $param['url_data_file'];
		// Сохраняем название файла с настройками сервера
		$filename = iconv(LANG_CHARSET, mb_internal_encoding() . "//IGNORE", $filename);

		$fp = fopen($filename, $mode);
		foreach ($list as $fields) {
			fputcsv($fp, $fields, $param['config_csv']['delimiter'], $param['config_csv']['enclosure']);
		}
		fclose($fp);
		// Если нужно ставим кодировку фафла
		self::SetCodingFile([
			'file' => $filename,
			'set_coding' => $param['config_csv']['export_coding']
		]);
		// Проверка записи
		if (file_exists($filename)) {
			return true;
		} else {
			throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE'));
		}
	}
	
	/**
	 * Устанавливает нужную кодировку
	 * @param array $param
	 */
	protected function SetCodingFile($param) {
		if (LANG_CHARSET != $param['set_coding']) {
			$f = file_get_contents($param['file']);
			$f = iconv(LANG_CHARSET, $param['set_coding'] . "//IGNORE", $f);
			file_put_contents($param['file'], $f);
		}
		
	}
	
	/**
	 * Готовит данные для записи
	 * @param array $param
	 * @return array
	 */
	protected function DataPreparation(array $param) {
		$list = [];
		if ($param['type'] == 'export_hl') {
			if (!empty($param['data']['hiblock'])) {
				foreach ($param['data']['hiblock'] as $key => $value) {
					$list[] = [ 'hiblock', $key, $value];
				}
			}
			if (!empty($param['data']['langs'])) {
				foreach ($param['data']['langs'] as $key => $value) {
					$list[] = [ 'langs', $key, $value];
				}
			}
			// Поля
			$field = [];
			if (count($param['data']['fields']) > 0 ) {
				$field = current($param['data']['fields']);
			}
			// Заголовки полей
			$list[] = ['fields'] + array_keys($field);
			// Значения полей
			foreach ($param['data']['fields'] as $key => $field) {
				$arr_field = [];
				foreach ($field as $value) {
					if (is_array($value)) {
						$arr_field[] = serialize($value);
					} else {
						$arr_field[] = $value;
					}
				}
				$list[] = ['fields'] + $arr_field;
			}
		} elseif ($param['type'] == 'export_data') {
			// Заголовки полей, только на первом шаге
			if ((int)$param['data']['step_id'] == 0) {
				$field = [];
				if (count($param['data']['fields']) > 0 ) {
					$field = current($param['data']['fields']);
				}
				$list[] = array_keys($field);
			}
			// Значения полей
			foreach ($param['data']['fields'] as $key => $field) {
				$arr_field = [];
				foreach ($field as $value) {
					if (is_array($value)) {
						$arr_field[] = implode($param['config_csv']['delimiter_m'], $value);
					} else {
						$arr_field[] = $value;
					}
				}
				$list[] = $arr_field;
			}
		}

		return $list;
	}

}
