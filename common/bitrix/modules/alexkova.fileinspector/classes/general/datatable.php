<?
use Alexkova\Fileinspector\StatisticTable as STable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
Loc::loadMessages(__FILE__);

class CAFIDataTable{

	public static function truncateTable(){

		global $DB;

		$strSQL = "TRUNCATE TABLE alexkova_fileinspector_statistic";
		$DB->Query($strSQL, false, __LINE__);

	}

}
