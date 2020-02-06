<?php
/**
 * Играет роль контролера
 */

use \Bitrix\Main\SystemException;
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

// Проверки
$prava = $APPLICATION->GetGroupRight("bendersay.exportimport");
$type = (int)filter_input(INPUT_POST, 'type', FILTER_SANITIZE_NUMBER_INT);
if ($type == 0 || !$prava >= "R") {
	throw new SystemException(Loc::getMessage('BENDERSAY_EXPORTIMPORT_ERROR'));
}
if (!\Bitrix\Main\Loader::includeModule('bendersay.exportimport')) {
	CAdminMessage::ShowMessage(GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_MODULE'));
	return false;
}

// Языковые файлы
Loc::loadMessages(__FILE__); 

$result = [];
$ob_ajax = new \Bendersay\Exportimport\Ajax();

switch ($type) {
	case 1:
		$ob_ajax->SendEmail();
		$result['status'] = true;
		$result['text'] = iconv(LANG_CHARSET,  "UTF-8//IGNORE", Loc::getMessage('BENDERSAY_EXPORTIMPORT_RESULT_TEXT'));
		break;
	case 2:
		$result['status'] = true;
		$ob = $ob_ajax->ObrFormCSV();
		$result['text'] = $ob['text'];
		$result['text'] = iconv(LANG_CHARSET,  "UTF-8//IGNORE", $result['text']);
		$result['fields_all_count'] = $ob['fields_all_count'];
		$result['fields_count'] = $ob['fields_count'];
		$result['step_id'] = $ob['step_id'];
		break;
	case 3:
		$result['status'] = true;
		$result['hl_id'] = $ob_ajax->GetUserEntity();
		break;
	case 4:
		$result['status'] = true;
		$ob = $ob_ajax->ObrFormJSON();
		$result['text'] = $ob['text'];
		$result['text'] = iconv(LANG_CHARSET,  "UTF-8//IGNORE", $result['text']);
		$result['fields_all_count'] = $ob['fields_all_count'];
		$result['fields_count'] = $ob['fields_count'];
		$result['step_id'] = $ob['step_id'];
		break;
	case 5:
		$res= $ob_ajax->GetUserEntityImport();
		$result['status'] = $res['status'];
		$result['text'] = $res['text'];
		break;
	case 6:
		$result['status'] = true;
		$ob = $ob_ajax->ImportDataJSON();
		$result['text'] = $ob['text'];
		$result['text'] = iconv(LANG_CHARSET,  "UTF-8//IGNORE", $result['text']);
		$result['fields_all_count'] = $ob['fields_all_count'];
		$result['fields_count'] = $ob['fields_count'];
		$result['step_id'] = $ob['step_id'];
		$result['import_error_count'] = $ob['import_error_count'];
		break;
	case 7:
		$res = $ob_ajax->GetKey();
		$result['status'] = $res['status'];
		$result['text'] = $res['text'];
		break;
	case 8:
		$ob = $ob_ajax->ImportDataCSV();
		$result['status'] = $ob['status'];
		$result['text'] = $ob['text'];
		$result['text'] = iconv(LANG_CHARSET,  "UTF-8//IGNORE", $result['text']);
		$result['fields_all_count'] = $ob['fields_all_count'];
		$result['fields_count'] = $ob['fields_count'];
		$result['step_id'] = $ob['step_id'];
		$result['import_error_count'] = $ob['import_error_count'];
				
		break;

	default:
		break;
}

// Выводим результат
echo json_encode($result,	JSON_FORCE_OBJECT);