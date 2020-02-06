<?php

namespace Bendersay\Exportimport;

use \Bitrix\Main\Localization\Loc;

/**
 * Класс для работы с почтой
 *
 * @author bender_say
 */
class Mail {

	public function __construct() {
		Loc::loadMessages(__FILE__);
	}

	/**
	 * Добавляет событие и шаблон модуля
	 * @return boolean
	 */
	static public function AddMailModule() {
		// Событие
		$et = new \CEventType;
		$et->Add(array(
			"LID" => SITE_ID,
			"EVENT_NAME" => 'BENDERSAY_EXPORTIMPORT_MAIL',
			"NAME" => Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL'),
			"DESCRIPTION" => Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL_DESCRIPTION')
		));
		// Шаблон
		$obSites = \Bitrix\Main\SiteTable::getList();
		$arr_sites = [];
		while ($arSite = $obSites->Fetch()) {
			$arr_sites[] = $arSite['LID'];
		}
		$arr["ACTIVE"] = "Y";
		$arr["EVENT_NAME"] = "BENDERSAY_EXPORTIMPORT_MAIL";
		$arr["LID"] = $arr_sites;
		$arr["EMAIL_FROM"] = '#DEFAULT_EMAIL_FROM#';
		$arr["EMAIL_TO"] = "#send_email#";
		$arr["SUBJECT"] = Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL_TITLE');
		$arr["BODY_TYPE"] = "text";
		$arr["MESSAGE"] = Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL_MESSAGE');
		$obTemplate = new \CEventMessage;
		$obTemplate->Add($arr);

		return false;
	}

	/**
	 * Удаляет шаблон и события модуля
	 * @return boolean
	 */
	static public function DeleteMailModule() {
		// Шаблон
		$emessage = new \CEventMessage;
		$rsMess = \CEventMessage::GetList($by = "id", $order = "asc", ['TYPE_ID' => 'BENDERSAY_EXPORTIMPORT_MAIL']);
		while ($arMess = $rsMess->GetNext()) {
			$emessage->Delete($arMess['ID']);
		}
		// Событие
		$et = new \CEventType;
		$et->Delete('BENDERSAY_EXPORTIMPORT_MAIL');
		return false;
	}

}
