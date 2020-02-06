<?
use Alexkova\Fileinspector\StatisticTable as STable;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);


class CAFIBFile{

	public static function checkFileList($type){

		$newCalc = new CAFITools();

		$types = CAFISearcher::getFT();
		$path = $types[$type];
		$seans = $type;

		$fromPosition = COption::GetOptionString('alexkova.fileinspector', 'current_'.$type.'_file');
		$fsize = COption::GetOptionString('alexkova.fileinspector', 'current_'.$type.'_filesize');
		$fcount = COption::GetOptionString('alexkova.fileinspector', 'current_'.$type.'_filecount');

		$arFields = array(
			"PATH"=>$path,
			"SEANCE"=>$seans,
			"FSIZE" => $fsize,
			"FCOUNT" => $fcount,
			"BREAK_POINT"=>$fromPosition
		);

		$arResult["RESULT"] = $newCalc->ScannerBot(
			$arFields
		);

		$arResult["RESULT"] = $arResult["RESULT"][$type];

		COption::SetOptionString('alexkova.fileinspector', 'current_'.$type.'_filesize', $arResult["RESULT"]["FSIZE"]);
		COption::SetOptionString('alexkova.fileinspector', 'current_'.$type.'_filecount', $arResult["RESULT"]["FCOUNT"]);
		COption::SetOptionString('alexkova.fileinspector', 'current_'.$type.'_file', $arResult["RESULT"]["BREAK_POINT"]);

		return $arResult;
	}

	public static function checkFileListFromDB($fromID){
		global $DB;
		global $operationID;

		$strSQL = "SELECT *, UNIX_TIMESTAMP(TIMESTAMP_X) as UTM FROM b_file WHERE ID>$fromID ORDER BY ID LIMIT 100";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = array();

		while ($arFields = $res->Fetch()){

			$newFields = $arFields;
			$newFields["DATE_MODIFY"] = \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields["UTM"]);;
			$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
			$newFields["BASE_ID"] = $arFields["ID"];

			$result = array(
				"DEFFECT_CODE" => '100',
				"DEFFECT_DETAIL" => 'unknown',
			);

			$path = $_SERVER["DOCUMENT_ROOT"]
				."/".COption::GetOptionString('main', 'upload_dir')
				."/".$arFields["SUBDIR"]
				."/".$arFields["FILE_NAME"];


			if (file_exists($path)){
				$searchResult = array(
					array(
						"TYPE" => 'B_FILE',
						"ID" => $arFields["ID"],
						"ABS_PATH" => $path,
					)
				);

				$checkFile = array(
					"DEFFECT_CODE" => '0',
					"DEFFECT_DETAIL" => '',
					'SEARCH_DETAIL' => serialize($searchResult)
				);
			}
			else{
				$deffectCode = GetMessage('DEFFECT_CODE');

				$checkFile = array(
					"DEFFECT_CODE" => '201001',
					"DEFFECT_DETAIL" => $deffectCode['201001'].'OBJECT_ID: ['.$newFields["BASE_ID"].'] '.$path,
				);
			}

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

	public static function checkFileListFromDBCount(){
		global $DB;

		$strSQL = "SELECT MAX(ID) AS MID FROM b_file";
		$res = $DB->Query($strSQL, false, __LINE__);

		$result = 0;

		if ($arFields = $res->Fetch()){
			$result = $arFields["MID"];
		}

		return $result;
	}

}