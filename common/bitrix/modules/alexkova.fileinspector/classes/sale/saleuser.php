<?php
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CIFSaleUser{

	public  static  function getStat(){

		global $DB, $APPLICATION;

		$result = array(
			"ERRORS" => array(),
			"MESSAGES" => array(),
			'CHECK_REFULL' => false
		);

		if (!CModule::IncludeModule('sale')){
			$result["ERRORS"][] = GetMessage('ERROR_SALE_FOUND');
			return $result;
		}

		$days = intval(COption::GetOptionString('sale', 'delete_after'));
		if (intval($days) <= 0){
			$result["ERRORS"][] = GetMessage('ERROR_SALE_DELETEAFTER_SETTINGS');
			return $result;
		}

		$strSQL = "
				SELECT
					COUNT(A.ID) AS CNT,
					MIN(A.DATE_UPDATE) as MIN_DATE,
					MAX(B.DATE_UPDATE) as MAX_DATE
				from b_sale_fuser A
					LEFT JOIN b_sale_order B ON (B.USER_ID = A.USER_ID)
				where
					TO_DAYS(A.DATE_UPDATE)<(TO_DAYS(NOW())-".$days.")
					AND B.ID is null
					AND A.USER_ID is null
		";

		$res = $DB->Query($strSQL, false, __FILE__);

		if ($arFields = $res->Fetch()){
			if ($arFields["CNT"]>100){
				$result['CHECK_REFULL'] = true;
				$str = GetMessage('ERROR_BSALEFUSER_REFULL');
				$str = str_replace("#CNT#", $arFields["CNT"], $str);
				$str = str_replace("#MIN_DATE#", $arFields["MIN_DATE"], $str);
				$str = str_replace("#MAX_DATE#", $arFields["MAX_DATE"], $str);
				$result['ERRORS'][] = $str;
				$result["CNT"] = $arFields["CNT"];
			}
		}

		return $result;
	}

}