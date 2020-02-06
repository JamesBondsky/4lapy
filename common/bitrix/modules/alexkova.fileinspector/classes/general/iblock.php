<?
use Alexkova\Fileinspector\StatisticTable as STable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
Loc::loadMessages(__FILE__);

class CAFIblockElementSearcher{

	public static function checkList($fromID){

		global $DB;
		global $operationID;

		$strSQL = "SELECT ID, PREVIEW_PICTURE, DETAIL_PICTURE FROM b_iblock_element WHERE ID>$fromID AND (PREVIEW_PICTURE>0 OR DETAIL_PICTURE>0) ORDER BY ID LIMIT 50";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = array();

		while ($arFields = $res->Fetch()){

			$IDS = array();
			if ($arFields["DETAIL_PICTURE"]>0) $IDS[] = $arFields["DETAIL_PICTURE"];
			if ($arFields["PREVIEW_PICTURE"]>0) $IDS[] = $arFields["PREVIEW_PICTURE"];

			foreach ($IDS as $val){

				$checkFile = CAFICheckFile::checkFileByID($val, $arFields["ID"], 'IBLOCK_ELEMENT');

				if (intval($checkFile["DEFFECT_CODE"]) <= 0){
					$newFields = $checkFile["FILE"];
					unset($newFields['~src']);
					unset($newFields['SRC']);
				}
				else {
					$newFields = array();
				}

				$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
				$newFields["BASE_ID"] = $val;
				$newFields["MODULE_ID"] = 'iblock_element';

				if ($checkFile["DEFFECT_CODE"]>0){
					$newFields["DEFFECT_CODE"] = $checkFile["DEFFECT_CODE"];
					$newFields["DEFFECT_DETAIL"] = $checkFile["DEFFECT_DETAIL"];
				}

				$newFields["SEARCH_DETAIL"] = $checkFile["SEARCH_DETAIL"];

				unset ($newFields["EXTERNAL_ID"]);
				unset ($newFields["ID"]);

				$newFields["OPERATION_ID"] = $operationID;

				$resultAdd = STable::add($newFields);

				if($resultAdd->isSuccess())
				{
					$id = $resultAdd->getId();
				}
				else
				{
					$result["ERROR"] = $resultAdd->getErrorMessages();;
				}

				$result["FIELDS"][] = $arFields;
				$result["LAST_ID"] = $arFields["ID"];
			}
		}

		return $result;
	}

	public static function checkSectionList($fromID){

		global $DB;
		global $operationID;

		$strSQL = "SELECT ID, PICTURE, DETAIL_PICTURE FROM b_iblock_section WHERE ID>$fromID AND (PICTURE>0 OR DETAIL_PICTURE>0) ORDER BY ID LIMIT 50";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = array();

		while ($arFields = $res->Fetch()){

			$IDS = array();
			if ($arFields["DETAIL_PICTURE"]>0) $IDS[] = $arFields["DETAIL_PICTURE"];
			if ($arFields["PICTURE"]>0) $IDS[] = $arFields["PICTURE"];

			foreach ($IDS as $val){

				$checkFile = CAFICheckFile::checkFileByID($val, $arFields["ID"], 'IBLOCK_SECTION');

				if (intval($checkFile["DEFFECT_CODE"]) <= 0){
					$newFields = $checkFile["FILE"];
					unset($newFields['~src']);
					unset($newFields['SRC']);
				}
				else {
					$newFields = array();
				}

				$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
				$newFields["BASE_ID"] = $val;
				$newFields["MODULE_ID"] = 'iblock_sections';

				if ($checkFile["DEFFECT_CODE"]>0){
					$newFields["DEFFECT_CODE"] = $checkFile["DEFFECT_CODE"];
					$newFields["DEFFECT_DETAIL"] = $checkFile["DEFFECT_DETAIL"];
				}

				$newFields["SEARCH_DETAIL"] = $checkFile["SEARCH_DETAIL"];

				unset ($newFields["EXTERNAL_ID"]);
				unset ($newFields["ID"]);

				$newFields["OPERATION_ID"] = $operationID;

				$resultAdd = STable::add($newFields);

				if($resultAdd->isSuccess())
				{
					$id = $resultAdd->getId();
				}
				else
				{
					$result["ERROR"] = $resultAdd->getErrorMessages();;
				}

				$result["FIELDS"][] = $arFields;
				$result["LAST_ID"] = $arFields["ID"];
			}
		}

		return $result;
	}

	public static function getCount(){

		global $DB;

		$strSQL = "SELECT MAX(ID) AS MID FROM b_iblock_element";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = 0;

		if ($arFields = $res->Fetch()){
			$result = $arFields["MID"];
		}

		return $result;
	}

	public static function getSectionCount(){

		global $DB;

		$strSQL = "SELECT MAX(ID) AS MID FROM b_iblock_section";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = 0;

		if ($arFields = $res->Fetch()){
			$result = $arFields["MID"];
		}

		return $result;
	}

}

class CAFIblockSearcher{

	public static function getAllFileProps(){

		global $PROPS;

		if (isset($_SESSION["AFSEARCHER_PROPS"])){
			$PROPS = $_SESSION["AFSEARCHER_PROPS"];
			return;
		}

		if (Cmodule::IncludeModule('iblock')){
			$result = array();

			$iblocks = CIBlock::GetList();

			$allBlocks = array();
			while ($arFields = $iblocks->GetNext())
			{
				$allBlocks[$arFields["ID"]] = array(
					"VERSION" => $arFields["VERSION"]
				);
			}

			$properties = CIBlockProperty::GetList(Array("iblock_id"=>"asc"), Array("PROPERTY_TYPE"=>"F"));
			while ($prop_fields = $properties->GetNext())
			{
				$mpl = $prop_fields["MULTIPLE"] == Y ? 1 : 0;
				$result[$allBlocks[$prop_fields["IBLOCK_ID"]]["VERSION"]][$mpl][$prop_fields["IBLOCK_ID"]][] = array(
					"ID" => $prop_fields["ID"],
				);
				$result["ALL"][$allBlocks[$prop_fields["IBLOCK_ID"]]["VERSION"]][] = $prop_fields["ID"];
			}

			$PROPS = $result;
			$_SESSION["AFSEARCHER_PROPS"] = $PROPS;
		}
	}

	public static function checkList($fromID){

		global $DB;
		global $operationID;

		$strSQL = "SELECT *, UNIX_TIMESTAMP(TIMESTAMP_X) as UTM FROM b_file WHERE ID>$fromID AND MODULE_ID = 'iblock' ORDER BY ID LIMIT 50";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = array();

		while ($arFields = $res->Fetch()){

			$newFields = $arFields;
			$newFields["DATE_MODIFY"] = \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields["UTM"]);;
			$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
			$newFields["BASE_ID"] = $arFields["ID"];

			$checkFile = CAFICheckFile::checkIblockBFile($arFields["ID"]);

			if ($checkFile["DEFFECT_CODE"]>0){
				$newFields["DEFFECT_CODE"] = $checkFile["DEFFECT_CODE"];
				$newFields["DEFFECT_DETAIL"] = $checkFile["DEFFECT_DETAIL"];
			}

			$newFields["SEARCH_DETAIL"] = $checkFile["SEARCH_DETAIL"];

			unset ($newFields["EXTERNAL_ID"]);
			unset ($newFields["UTM"]);
			unset ($newFields["ID"]);

			$newFields["OPERATION_ID"] = $operationID;

			$resultAdd = STable::add($newFields);

			if($resultAdd->isSuccess())
			{
				$id = $resultAdd->getId();
			}
			else
			{
				$result["ERROR"] = $resultAdd->getErrorMessages();;
			}

			$result["FIELDS"][] = $arFields;
			$result["LAST_ID"] = $arFields["ID"];
		}

		return $result;
	}

	public static function getCount(){

		global $DB;

		$strSQL = "SELECT MAX(ID) AS MID FROM b_file WHERE MODULE_ID = 'iblock'";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = 0;

		if ($arFields = $res->Fetch()){
			$result = $arFields["MID"];
		}

		return $result;

	}

}