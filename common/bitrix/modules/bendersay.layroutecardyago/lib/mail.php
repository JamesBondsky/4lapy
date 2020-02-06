<?php

namespace Bendersay\Exportimport;

use \Bitrix\Main\Localization\Loc;

/**
 * ����� ��� ������ � ������
 *
 * @author bender_say
 */
class Mail {

	public function __construct() {
		Loc::loadMessages(__FILE__);
	}

	/**
	 * ��������� ������� � ������ ������
	 * @return boolean
	 */
	static public function AddMailModule() {
		// �������
		$et = new \CEventType;
		$et->Add(array(
			"LID" => SITE_ID,
			"EVENT_NAME" => 'BENDERSAY_EXPORTIMPORT_MAIL',
			"NAME" => Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL'),
			"DESCRIPTION" => Loc::GetMessage('BENDERSAY_EXPORTIMPORT_MAIL_DESCRIPTION')
		));
		// ������
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
	 * ������� ������ � ������� ������
	 * @return boolean
	 */
	static public function DeleteMailModule() {
		// ������
		$emessage = new \CEventMessage;
		$rsMess = \CEventMessage::GetList($by = "id", $order = "asc", ['TYPE_ID' => 'BENDERSAY_EXPORTIMPORT_MAIL']);
		while ($arMess = $rsMess->GetNext()) {
			$emessage->Delete($arMess['ID']);
		}
		// �������
		$et = new \CEventType;
		$et->Delete('BENDERSAY_EXPORTIMPORT_MAIL');
		return false;
	}

}
