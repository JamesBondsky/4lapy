<?php

namespace Bendersay\Exportimport;

use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

/**
 * Description of ajax
 *
 * @author bender_say
 */
class Ajax {
	
	public $request;
	public $response;

	public function __construct() {
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$this->request = $context->getRequest();
		$this->response = $context->getResponse();
	}

	/**
	 * Отправка Файла на email
	 */
	function SendEmail() {
		// Получаем POST
		$args = array(
			'email' => FILTER_SANITIZE_STRING,
			'file' => FILTER_SANITIZE_STRING,
			'hl_id' => FILTER_SANITIZE_NUMBER_INT
		);
		$filter_post = filter_input_array(INPUT_POST, $args);
		// Готовим данные
		$obSites = \Bitrix\Main\SiteTable::getList();
		$arr_sites = [];
		while ($arSite = $obSites->Fetch()) {
			$arr_sites[] = $arSite['LID'];
		}

		// Перед отправкой письма сохраняем файлы в битрикс, чтобы прикрепить их к письму.
		$arr_id_files = array();
		$arr_file[] = \Bitrix\Main\Application::getDocumentRoot() . base64_decode($filter_post['file']);
		foreach ($arr_file as $value) {
			$arr_id_files[] = \CFile::SaveFile(\CFile::MakeFileArray(iconv(LANG_CHARSET, mb_internal_encoding() . "//IGNORE", $value)), "tmp_bendersay_exportimport");
		}
		$arEventFields = array('param' => (int) $filter_post['hl_id'], 'send_email' => $filter_post['email']);
		// Отправка
		\CEvent::sendImmediate('BENDERSAY_EXPORTIMPORT_MAIL', $arr_sites, $arEventFields, 'N', false, $arr_id_files);
		// После отправки писем, удаляем файлы
		if (!empty($arr_id_files)) {
			foreach ($arr_id_files as $value) {
				\CFile::Delete($value);
			}
		}
		/* НЕЛЬЗЯ отправить файлы :-(
		 * \Bitrix\Main\Mail\Event::sendImmediate();
		 */
	}

	/**
	 * Экспорт HL
	 * @return array
	 */
	function ObrFormCSV() {

		$method = $this->request->getRequestMethod();
		$ob_ExportCSV = new \Bendersay\Exportimport\ExportCSV();
		$result = [];

		// Сохраняем POST
		$url_data_file = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));
		$hl_id = (int) $this->request->getPost('hl_id');
		$export_type = $this->request->getPost('export_type');
		$export_count_row = (int) $this->request->getPost('export_count_row');
		$export_step_id = (int) $this->request->getPost('export_step_id');
		$export_select = (array) $this->request->getpost('export_userentity');

		$config_csv = [
			'delimiter' => $this->request->getPost('delimiter'),
			'enclosure' => $this->request->getPost('enclosure'),
			'export_coding' => $this->request->getPost('export_coding'),
			'delimiter_m' => $this->request->getPost('delimiter_m')
		];

		// Если все норм работаем
		if ($method == 'POST') {
			ob_start();
			if (!empty($url_data_file) && $hl_id > 0 && !empty($export_type) && check_bitrix_sessid() && $export_count_row > 0) {

				if ($export_type == 'export_hl') {
					$data = $ob_ExportCSV->GetHlStructure($hl_id);
					$res_save_file = $ob_ExportCSV->SaveCSV([
						'type' => 'export_hl',
						'url_data_file' => $url_data_file,
						'data' => $data,
						'config_csv' => $config_csv
					]);
					if ($res_save_file) {
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE') . '<a href="' . $url_data_file . '">' . $url_data_file . '</a>'
							. '<br>' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND')
							. '<input type="text" value="' . Option::get("bendersay.exportimport", "export_send_email") . '"> '
							. '<input type="submit" id="send_email" data-file="' . base64_encode($url_data_file) . '" data-hl_id="' . $hl_id . '"'
							. ' value="' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND_BUTTON') . '" class="adm-btn-save" >'
							. '<br><div id="bendersay_exportimport_result"></div>',
							'TYPE' => 'OK',
							'HTML' => true
						]);
					} else {
						// Завершаем запросы
						$result['step_id'] = $data['fields_all_count'];
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_ERROR'),
							'TYPE' => 'ERROR',
							'HTML' => true
						]);
					}
				}

				if ($export_type == 'export_data') {
					$arr_step['limit'] = $export_count_row;
					$arr_step['step_id'] = $export_step_id;
					$data = $ob_ExportCSV->GetHlData($hl_id, $arr_step, $export_select);

					// сохраняем шаги
					$result['fields_all_count'] = $data['fields_all_count'];
					$result['fields_count'] = $data['fields_count'];
					$result['step_id'] = $data['step_id'];
					// если не дошли до конца, прогресс бар
					if ($data['fields_count'] < $data['fields_all_count']) {
						\CAdminMessage::ShowMessage(array(
							"MESSAGE" => Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR'),
							"DETAILS" => "#PROGRESS_BAR#",
							"HTML" => true,
							"TYPE" => "PROGRESS",
							"PROGRESS_TOTAL" => $data['fields_all_count'],
							"PROGRESS_VALUE" => $data['fields_count'],
						));
					} else {
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE') . '<a href="' . $url_data_file . '">' . $url_data_file . '</a>'
							. '<br>' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND')
							. '<input type="text" value="' . Option::get("bendersay.exportimport", "export_send_email") . '"> '
							. '<input type="submit" id="send_email" data-file="' . base64_encode($url_data_file) . '" data-hl_id="' . $hl_id . '"'
							. ' value="' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND_BUTTON') . '" class="adm-btn-save" >'
							. '<br><div id="bendersay_exportimport_result"></div>',
							'TYPE' => 'OK',
							'HTML' => true
						]);
					}
					// Сохраняем результат
					$data['step_id'] = $export_step_id;
					$res_save_file = $ob_ExportCSV->SaveCSV([
						'type' => 'export_data',
						'url_data_file' => $url_data_file,
						'data' => $data,
						'config_csv' => $config_csv
					]);
					if ($res_save_file) {
						
					} else {
						// Завершаем запросы
						$result['step_id'] = $data['fields_all_count'];
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_ERROR'),
							'TYPE' => 'ERROR',
							'HTML' => true
						]);
					}
				}
			} else {
				\CAdminMessage::ShowMessage(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_EMPTY'));
			}
			$result['text'] = ob_get_clean();
		}
		return $result;
	}

	/**
	 * Экспорт HL
	 * @return array
	 */
	function ObrFormJSON() {

		$method = $this->request->getRequestMethod();
		$ob_ExportCSV = new \Bendersay\Exportimport\ExportJSON();
		$result = [];

		// Сохраняем POST
		$url_data_file = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));
		$hl_id = (int) $this->request->getPost('hl_id');
		$export_type = $this->request->getPost('export_type');
		$export_count_row = (int) $this->request->getPost('export_count_row');
		$export_step_id = (int) $this->request->getPost('export_step_id');
		$export_select = (array) $this->request->getpost('export_userentity');

		// Если все норм работаем
		if ($method == 'POST') {
			ob_start();
			if (!empty($url_data_file) && $hl_id > 0 && !empty($export_type) && check_bitrix_sessid() && $export_count_row > 0) {

				if ($export_type == 'export_hl') {
					$data = $ob_ExportCSV->GetHlStructure($hl_id);
					$res_save_file = $ob_ExportCSV->SaveJSON([
						'type' => 'export_hl',
						'url_data_file' => $url_data_file,
						'data' => $data
					]);
					if ($res_save_file) {
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_JSON') . '<a href="' . $url_data_file . '"  download>' . $url_data_file . '</a>'
							. '<br>' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND')
							. '<input type="text" value="' . Option::get("bendersay.exportimport", "export_send_email") . '"> '
							. '<input type="submit" id="send_email" data-file="' . base64_encode($url_data_file) . '" data-hl_id="' . $hl_id . '"'
							. ' value="' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND_BUTTON') . '" class="adm-btn-save" >'
							. '<br><div id="bendersay_exportimport_result"></div>',
							'TYPE' => 'OK',
							'HTML' => true
						]);
					} else {
						// Завершаем запросы
						$result['step_id'] = $data['fields_all_count'];
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_ERROR'),
							'TYPE' => 'ERROR',
							'HTML' => true
						]);
					}
				}

				if ($export_type == 'export_data') {
					$arr_step['limit'] = $export_count_row;
					$arr_step['step_id'] = $export_step_id;
					$data = $ob_ExportCSV->GetHlData($hl_id, $arr_step, $export_select);

					// сохраняем шаги
					$result['fields_all_count'] = $data['fields_all_count'];
					$result['fields_count'] = $data['fields_count'];
					$result['step_id'] = $data['step_id'];
					// если не дошли до конца, прогресс бар
					if ($data['fields_count'] < $data['fields_all_count']) {
						\CAdminMessage::ShowMessage(array(
							"MESSAGE" => Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR'),
							"DETAILS" => "#PROGRESS_BAR#",
							"HTML" => true,
							"TYPE" => "PROGRESS",
							"PROGRESS_TOTAL" => $data['fields_all_count'],
							"PROGRESS_VALUE" => $data['fields_count'],
						));
					} else {
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_JSON') . '<a href="' . $url_data_file . '" download>' . $url_data_file . '</a>'
							. '<br>' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND')
							. '<input type="text" value="' . Option::get("bendersay.exportimport", "export_send_email") . '"> '
							. '<input type="submit" id="send_email" data-file="' . base64_encode($url_data_file) . '" data-hl_id="' . $hl_id . '"'
							. ' value="' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_SEND_BUTTON') . '" class="adm-btn-save" >'
							. '<br><div id="bendersay_exportimport_result"></div>',
							'TYPE' => 'OK',
							'HTML' => true
						]);
					}
					// Сохраняем результат
					$data['step_id'] = $export_step_id;
					try {
						$res_save_file = $ob_ExportCSV->SaveJSON([
							'type' => 'export_data',
							'url_data_file' => $url_data_file,
							'data' => $data
						]);
					} catch (SystemException $exception) {
						\CAdminMessage::ShowMessage([
							'MESSAGE' => $exception->getMessage(),
							'TYPE' => 'ERROR',
							'HTML' => true
						]);
					}
					
					if ($res_save_file) {
						
					} else {
						// Завершаем запросы
						$result['fields_count'] = $data['fields_all_count'];
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SAVE_ERROR'),
							'TYPE' => 'ERROR',
							'HTML' => true
						]);
					}
				}
			} else {
				\CAdminMessage::ShowMessage(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_EMPTY'));
			}
			$result['text'] = ob_get_clean();
		}
		return $result;
	}
	
	
	/**
	 * Импорт JSON
	 * @return type
	 */
	public function ImportDataJSON() {
		$method = $this->request->getRequestMethod();
		$ob_import_JSON = new \Bendersay\Exportimport\ImportJSON();
		$result = [];

		// Сохраняем POST
		$param = [];
		$param['url_data_file'] = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));
		$param['hl_id'] = (int) $this->request->getPost('hl_id_data');
		$param['export_type'] = $this->request->getPost('export_type');
		$param['export_count_row'] = (int) $this->request->getPost('export_count_row');
		$param['import_error_count'] = (int) $this->request->getPost('import_error_count');
		$param['arr_step']['export_step_id'] = (int) $this->request->getPost('export_step_id');
		$param['arr_step']['FIELDS'] = (array) $this->request->getpost('FIELDS');
		$param['import_key'] = $this->request->getpost('import_key');
		
		// Если все норм работаем
		if ($method == 'POST') {
			ob_start();
			if (!empty($param['url_data_file']) && $param['hl_id'] > 0 && !empty($param['export_type']) && check_bitrix_sessid() && $param['export_count_row'] > 0) {

				if ($param['export_type'] == 'export_hl') {
					?><div class="adm-info-message">
						<p><?=Loc::getMessage('BENDERSAY_EXPORTIMPORT_ZAGLUSHKA');?></p>
					</div><?
				}

				if ($param['export_type'] == 'export_data') {
					// Сбор доп. данных
					$arr_import = $this->ProvFileJSON();

					foreach (\Bendersay\Exportimport\Helper::GetUserEntity($param['hl_id']) as $field) {
						$param['arr_step']['FIELDS_TYPE'][$field['FIELD_NAME']] = $field['USER_TYPE_ID'];
					}

					if ($arr_import['status'] === true) {
						$param['data'] = $arr_import['arr'];
						$data = $ob_import_JSON->ImportDataJSON($param);
						// Запишем логи ошибок, и счетчик
						if (!empty($data['error'])) {
							file_put_contents(
								\Bitrix\Main\Application::getDocumentRoot() . '/upload/tmp_bendersay_exportimport/ImportLog.txt',
								print_r($data['error'], true),
								($param['arr_step']['export_step_id'] == 0 ? NULL : FILE_APPEND)
								);
							$result['import_error_count'] = $param['import_error_count']  + count($data['error']);

						} else {
							$result['import_error_count'] = $param['import_error_count'];
						}

					} 

					// сохраняем шаги
					$result['fields_all_count'] = $arr_import['arr']['items_all_count'];
					$result['fields_count'] = $data['fields_count'];
					$result['step_id'] = $data['step_id'];
					
					// если не дошли до конца, прогресс бар
					if ($result['fields_count'] < $result['fields_all_count']) {
						\CAdminMessage::ShowMessage(array(
							"MESSAGE" => Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT') 
							. ' ' . $result['fields_count'] . ' ' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT_IZ') 
							. ' ' .  $result['fields_all_count'],
							"DETAILS" => "#PROGRESS_BAR#",
							"HTML" => true,
							"TYPE" => "PROGRESS",
							"PROGRESS_TOTAL" => $result['fields_all_count'],
							"PROGRESS_VALUE" => $result['fields_count'],
						));
					} else {
						if ($result['import_error_count'] > 0) {
							\CAdminMessage::ShowMessage([
								'MESSAGE' => str_replace('#count#', $result['import_error_count'], Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_IMPORT_FILE'))
								. '<a href="/upload/tmp_bendersay_exportimport/ImportLog.txt" target="_blank">ImportLog.txt</a>',
								'TYPE' => 'ERROR',
								'HTML' => true
							]);
						}
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_FINISH_IMPORT'), 
							'TYPE' => 'OK',
							'HTML' => true
						]);
					}

				}
			} else {
				\CAdminMessage::ShowMessage(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_EMPTY'));
			}
			$result['text'] = ob_get_clean();
		}
		return $result;
	}
	
	
	public function ImportDataCSV() {
		$method = $this->request->getRequestMethod();
		$ob_import_CSV = new \Bendersay\Exportimport\ImportCSV();
		$result = [];

		// Сохраняем POST
		$param = [];
		$param['url_data_file'] = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));
		$param['hl_id'] = (int) $this->request->getPost('hl_id_data');
		$param['export_type'] = $this->request->getPost('export_type');
		$param['export_count_row'] = (int) $this->request->getPost('export_count_row');
		$param['import_error_count'] = (int) $this->request->getPost('import_error_count');
		$param['export_coding'] = $this->request->getPost('export_coding');
		$param['arr_step']['export_step_id'] = (int) $this->request->getPost('export_step_id');
		$param['arr_step']['FIELDS'] = (array) $this->request->getpost('FIELDS');
		$param['CSV']['delimiter'] = $this->request->getpost('delimiter');
		$param['CSV']['enclosure'] = $this->request->getpost('enclosure');
		$param['CSV']['delimiter_m'] = $this->request->getpost('delimiter_m');
		$param['import_key'] = $this->request->getpost('import_key');
		
		// Если все норм работаем
		if ($method == 'POST') {
			ob_start();
			if (!empty($param['url_data_file']) && $param['hl_id'] > 0 && !empty($param['export_type']) && check_bitrix_sessid() && $param['export_count_row'] > 0) {

				if ($param['export_type'] == 'export_hl') {
					?><div class="adm-info-message">
						<p><?=Loc::getMessage('BENDERSAY_EXPORTIMPORT_ZAGLUSHKA');?></p>
					</div><?
				}

				if ($param['export_type'] == 'export_data') {
					// Сбор доп. данных
					$arr_import = $this->ProvFileCSV();

					foreach (\Bendersay\Exportimport\Helper::GetUserEntity($param['hl_id']) as $field) {
						$param['arr_step']['FIELDS_TYPE'][$field['FIELD_NAME']]['USER_TYPE_ID'] = $field['USER_TYPE_ID'];
						$param['arr_step']['FIELDS_TYPE'][$field['FIELD_NAME']]['MULTIPLE'] = $field['MULTIPLE'];
					}

					if ($arr_import['status'] === true) {
						$param['data'] = $arr_import['arr'];
						$data = $ob_import_CSV->ImportDataCSV($param);

						// Запишем логи ошибок, и счетчик
						if (!empty($data['error'])) {
							file_put_contents(
								\Bitrix\Main\Application::getDocumentRoot() . '/upload/tmp_bendersay_exportimport/ImportLog.txt',
								print_r($data['error'], true),
								($param['arr_step']['export_step_id'] == 0 ? NULL : FILE_APPEND)
								);
							$result['import_error_count'] = $param['import_error_count']  + count($data['error']);

						} else {
							$result['import_error_count'] = $param['import_error_count'];
						}

					} 

					// сохраняем шаги
					$result['fields_all_count'] = $ob_import_CSV->GetAllItemsCount($param['url_data_file']);
					$result['fields_count'] = $data['fields_count'];
					$result['step_id'] = $data['step_id'];
					$result['status'] = true;
					
					// если не дошли до конца, прогресс бар
					if ($result['fields_count'] < $result['fields_all_count']) {
						\CAdminMessage::ShowMessage(array(
							"MESSAGE" => Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT') 
							. ' ' . $result['fields_count'] . ' ' . Loc::getMessage('BENDERSAY_EXPORTIMPORT_PROGRESS_BAR_IMPORT_IZ') 
							. ' ' .  $result['fields_all_count'],
							"DETAILS" => "#PROGRESS_BAR#",
							"HTML" => true,
							"TYPE" => "PROGRESS",
							"PROGRESS_TOTAL" => $result['fields_all_count'],
							"PROGRESS_VALUE" => $result['fields_count'],
						));
					} else {
						if ($result['import_error_count'] > 0) {
							$result['status'] = false;
							\CAdminMessage::ShowMessage([
								'MESSAGE' => str_replace('#count#', $result['import_error_count'], Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_IMPORT_FILE'))
								. '<a href="/upload/tmp_bendersay_exportimport/ImportLog.txt" target="_blank">ImportLog.txt</a>',
								'TYPE' => 'ERROR',
								'HTML' => true
							]);
						}
						\CAdminMessage::ShowMessage([
							'MESSAGE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_FINISH_IMPORT'), 
							'TYPE' => 'OK',
							'HTML' => true
						]);
					}

				}
			} else {
				\CAdminMessage::ShowMessage(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_EMPTY'));
			}
			$result['text'] = ob_get_clean();
		}
		return $result;
	}

	function GetUserEntity() {

		$method = $this->request->getRequestMethod();
		$result = [];

		// Сохраняем POST
		$hl_id = (int) $this->request->getPost('hl_id');
		if ($hl_id > 0) {
			$res = \Bendersay\Exportimport\Helper::GetUserEntity($hl_id);
		}
		if (!empty($res)) {
			foreach ($res as $value) {
				$result[$value['FIELD_NAME']] = iconv(LANG_CHARSET, "UTF-8//IGNORE", $value['langs']['ru']['EDIT_FORM_LABEL'] . ' [<b>' . $value['FIELD_NAME'] . '</b>] (' . $value['ID'] . ')');
			}
		}
		return $result;
	}
	
	/**
	 * Смена HL при импорте данных,
	 * Соответствие полей из Highload-блока полям из файла
	 * @return string
	 */
	function GetUserEntityImport() {

		if ($this->request->getPost('type_import') == 8) {
			$result = $this->ProvFileCSV();
		} elseif ($this->request->getPost('type_import') == 6) {
			$result = $this->ProvFileJSON();
		}
		
		if ($result['status'] === false) {
			return ['status' => false, 'text' => $result['text']];
		} else {
			$arr_import = $result['arr'];
		}
		
		if (is_array($arr_import)) {
			$str = '<table>';
			$str .= iconv(LANG_CHARSET, "UTF-8//IGNORE", GetMessage('BENDERSAY_EXPORTIMPORT_GETUSERENTITYIMPORT_ZAG'));
			$arr = $this->GetUserEntity();
			foreach ($arr as $key => $value) {
				$str .= '<tr>';
				$str .= '<td class="adm-detail-content-cell-l">' . $value . ': </td>'
					. '<td><select name="FIELDS[' . $key . ']">'
					. '<option value=""></option>';
				foreach ($arr_import['fields_name'] as $v_imp) {
					$str .= '<option value="' . $v_imp . '" ' . ($key == $v_imp ? 'selected' : '') . '>' . $v_imp . '</option>';
				}
				$str .= '</select></td>';
				$str .= '</tr>';
			}
			$str .= '</table>';
		} else {
			$str = $arr_import;
		}

		return ['status' => true, 'text' => $str];
	}
	
	/**
	 * Проверяем наличие ключей
	 * @param type $arr
	 * @return boolean
	 * @throws SystemException
	 */
	function ProverkaFields($arr) {
		if (is_array($arr)) {
			if (!array_key_exists('fields_name', $arr)) {
				throw new SystemException(str_replace('#key#', 'fields_name', Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_NOT_KEY')));
			}
			if (!array_key_exists('items_all_count', $arr)) {
				throw new SystemException(str_replace('#key#', 'items_all_count', Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_NOT_KEY')));
			}
			if (!array_key_exists('items', $arr)) {
				throw new SystemException(str_replace('#key#', 'items', Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_NOT_KEY')));
			}
			if ($arr['items_all_count'] != count($arr['items'])) {
				throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR_COUNT'));
			}
			return true;
		}
		return false;
	}
	
	/**
	 * возвращает HTML
	 * @return string
	 */
	function GetKey() {
		
		if ($this->request->getPost('export_type') == 'export_hl') {
			$str = '<div class="adm-info-message">';
			$str .= '<p>' . iconv(LANG_CHARSET, "UTF-8//IGNORE", Loc::getMessage('BENDERSAY_EXPORTIMPORT_ZAGLUSHKA')) . '</p>';
			$str .= '</div>';
			return ['status' => false, 'text' => $str];
		}
		
		$str = '';
		if ($this->request->getPost('type_import') == 8) {
			$result = $this->ProvFileCSV();
		} elseif ($this->request->getPost('type_import') == 6) {
			$result = $this->ProvFileJSON();
		}

		if ($result['status'] == false) {
			return ['status' => false, 'text' => $result['text']];
		}
		
		if (is_array($result['arr'])) {
			$str =  '<select name="import_key">'
					. '<option value=""></option>';
			foreach ($result['arr']['fields_name'] as $v_imp) {
				$str .= '<option value="' . $v_imp . '" >' . $v_imp . '</option>';
			}
			$str .= '</select>';
		}
		return ['status' => true, 'text' => $str];
	}
	
	/**
	 * Читает файл и проверяет поля JSON
	 * @return array
	 */
	function ProvFileJSON() {

		$filename = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));
		$json = file_get_contents(\Bitrix\Main\Application::getDocumentRoot() . $filename);

		// Если нет файла для ипорта
		if ($json === false || strlen($filename) == 0) {
			ob_start();
			\CAdminMessage::ShowMessage(iconv(LANG_CHARSET, "UTF-8//IGNORE", GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_GETUSERENTITYIMPORT')));
			return ['status' => false, 'text' => ob_get_clean()];
		}
		
		// Проверяем размер файла и доступной оперативы
		$memory_limit = \Bendersay\Exportimport\Helper::GetBytes(ini_get('memory_limit'));
		$file_size = filesize(\Bitrix\Main\Application::getDocumentRoot() . $filename);
		if ((memory_get_usage() + $file_size * 7) > $memory_limit) {
			ob_start();
			\CAdminMessage::ShowMessage(iconv(LANG_CHARSET, "UTF-8//IGNORE", 
				str_replace(['#file_size#', '#memory_limit#'], [round($file_size/1024/1024, 1, PHP_ROUND_HALF_UP), ini_get('memory_limit')], GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_FILE_SIZE'))));
			return ['status' => false, 'text' => ob_get_clean()];
		}
		
		// проверка полей $arr_import['fields_name']
		try {
			$arr_import = json_decode($json, true);

			if ($this->ProverkaFields($arr_import)) {
				return ['status' => true, 'arr' => $arr_import];
			}
		} catch (SystemException $exception) {
			ob_start();
			\CAdminMessage::ShowMessage(iconv(LANG_CHARSET, "UTF-8//IGNORE", $exception->getMessage()));
			return ['status' => false, 'text' => ob_get_clean()];
		}
	}
	
	/**
	 * Проверяет наличие CSV и возврщает первую строку(поля)
	 * @return type
	 */
	function ProvFileCSV() {

		$filename = iconv(mb_internal_encoding(), LANG_CHARSET . "//IGNORE", $this->request->getPost('url_data_file'));

		// Если нет файла для ипорта
		if (!file_exists(\Bitrix\Main\Application::getDocumentRoot() . $filename)) {
			ob_start();
			\CAdminMessage::ShowMessage(iconv(LANG_CHARSET, "UTF-8//IGNORE", GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_GETUSERENTITYIMPORT')));
			return ['status' => false, 'text' => ob_get_clean()];
		} else {
			$arr_import = [];
			if (($handle = fopen(\Bitrix\Main\Application::getDocumentRoot() . $filename, "r")) !== FALSE) {
				$data = fgetcsv($handle, 0, ";");
				$num = count($data);
				for ($c = 0; $c < $num; $c++) {
					$arr_import['fields_name'][$c] = $data[$c];
				}
				fclose($handle);
			}
			return ['status' => true, 'arr' => $arr_import];
		}

	}

}
