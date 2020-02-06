<?
use Alexkova\Fileinspector\StatisticTable as STable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
Loc::loadMessages(__FILE__);


class CAFISearcher
{
	public static $startModule = 'IBLOCK_DB';

	public static function getFT(){

		return array(
			'iblock' => $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString('main','upload_dir')."/iblock/"
		);

	}

	public static function getSM(){

		$SM = array(
			'IBLOCK_DB' => array(
				"MODULE_TITLE"=> GetMessage("FILEINSPECTOR_SEARCH_IBLOCKDB_TITLE"),
				"NAME" => GetMessage("FILEINSPECTOR_SEARCH_IBLOCKDB_NAME"),
				"START_ATTRIBUTES" => array(
					"current_iblock_id" => 0,
					'may_be_continue' => 1
				),
				"NEXT_MODULE" => 'IBLOCK_ELEMENTS'
			),

			'IBLOCK_ELEMENTS' => array(
				"MODULE_TITLE"=> GetMessage("FILEINSPECTOR_SEARCH_IBLOCKELEMENTS_TITLE"),
				"NAME" => GetMessage("FILEINSPECTOR_SEARCH_IBLOCKELEMENTS_NAME"),
				"START_ATTRIBUTES" => array(
					"current_element_id" => 0,
					'may_be_continue' => 1
				),
				"NEXT_MODULE" => 'IBLOCK_SECTIONS'
			),

			'IBLOCK_SECTIONS' => array(
				"MODULE_TITLE"=> GetMessage("FILEINSPECTOR_SEARCH_IBLOCKSECTION_TITLE"),
				"NAME" => GetMessage("FILEINSPECTOR_SEARCH_IBLOCKSECTION_NAME"),
				"START_ATTRIBUTES" => array(
					"current_section_id" => 0,
					'may_be_continue' => 1
				),
				"NEXT_MODULE" => 'IBLOCK_FILE'
			),

			'IBLOCK_FILE' => array(
				"MODULE_TITLE"=> GetMessage("FILEINSPECTOR_SEARCH_IBLOCKFILE_TITLE"),
				"NAME" => GetMessage("FILEINSPECTOR_SEARCH_IBLOCKFILE_NAME"),
				"START_ATTRIBUTES" => array(
					"current_iblock_file" => '',
					'may_be_continue' => 1,
					'current_iblock_filecount' => 0,
					'current_iblock_filesize' => 0,
				),
				"NEXT_MODULE" => 'B_FILE'
			),

			'B_FILE' => array(
				"MODULE_TITLE"=> GetMessage("FILEINSPECTOR_SEARCH_BFILE_TITLE"),
				"NAME" => GetMessage("FILEINSPECTOR_SEARCH_BFILE_NAME"),
				"START_ATTRIBUTES" => array(
					"current_bfile_id" => '',
				),
				"NEXT_MODULE" => ''
			),
		);

		return $SM;
	}

	public static function getState(){

		$currentState = COption::GetOptionString('alexkova.fileinspector', 'state');

		$arReturn = array(
			'STATE' => $currentState
		);

		if ($currentState == 'success'){
			$currentDateSuccess = COption::GetOptionString('alexkova.fileinspector', 'date_success');
			$arReturn['DATE_SUCCESS'] = $currentDateSuccess;
		}

		if ($currentState == 'error'){
			$lastError = COption::GetOptionString('alexkova.fileinspector', 'last_error');
			$arReturn['LAST_ERROR'] = unserialize($lastError);
		}

		$arReturn['LAST_OPERATION'] = COption::GetOptionString('alexkova.fileinspector', 'last_scan_operation');


		return $arReturn;

	}

	public static function refreshActuality(){

		COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', time());
	}

	public static function checkSuccess($currentType){

		if (COption::GetOptionString('alexkova.fileinspector', strtolower('scan_'.$currentType)) == "Y"){
			return true;
		}
		else{
			return false;
		}
	}

	public static function scanNextModule($currentType){

		$searchModules = self::getSM();

		COption::SetOptionString('alexkova.fileinspector', 'type', $searchModules[$currentType]["NEXT_MODULE"]);
		if (strlen($searchModules[$currentType]["NEXT_MODULE"]) > 0){
			foreach($searchModules[$searchModules[$currentType]["NEXT_MODULE"]]["START_ATTRIBUTES"] as $cell=>$val){
				COption::SetOptionString('alexkova.fileinspector', $cell, $val);
			}

			$ms = GetMessage('FILEINSPECTOR_SEARCH_STATE_DETAILS');
			$txt = GetMessage('FILEINSPECTOR_SEARCH_NEXT_STEP');

			if (strlen($searchModules[$currentType]["NEXT_MODULE"])> 0){
				$txt = GetMessage('FILEINSPECTOR_SEARCH_NEXT')." ".$ms[$searchModules[$currentType]["NEXT_MODULE"]];
			}

			$message = array(
				"TYPE" => "OK",
				"MESSAGE" => GetMessage('FILEINSPECTOR_SEARCH_STATE')." ".$ms[$currentType],
				"DETAILS" => $txt,
				"HTML" => true
			);

			return $message;
		}
	}

	public static function nextStep(){

		global $operationID;

		$searchModules = self::getSM();

		$result = array(
			'progress' => 'stop'
		);

		$currentState = COption::GetOptionString('alexkova.fileinspector', 'state');
		$currentType = COption::GetOptionString('alexkova.fileinspector', 'type');

		if ($currentState == 'success')  return array(
													'progress' => 'success'
												);

		if ($currentState == 'start' || strlen($currentState) <= 0){

			COption::SetOptionString('alexkova.fileinspector', 'type', self::$startModule);
			COption::SetOptionString('alexkova.fileinspector', 'state', 'progress');
			$currentType = self::$startModule;

			CAFIDataTable::truncateTable();
			unset($_SESSION["AFSEARCHER_PROPS"]);
		}

		if ($currentState == 'stop'){
			return array(
				"progress" => "stop"
				);
		}

		$operationID = $currentType;

		switch($currentType){
			case "IBLOCK_DB":

				if (self::checkSuccess($currentType)){

					$allCount = CAFIblockSearcher::getCount();
					$currentValues = CAFIblockSearcher::checkList(intval(COption::GetOptionString('alexkova.fileinspector', 'current_iblock_id')));

					COption::SetOptionString('alexkova.fileinspector', 'current_iblock_id', $currentValues["LAST_ID"]);
					COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', 'IBLOCK_ID');

					if (count($currentValues["FIELDS"])>0 && count($currentValues["ERROR"]) <= 0){
						$result["PROGRESS_VALUE"] = $currentValues["LAST_ID"] / $allCount * 100;
						$result["DETAILS"] = $searchModules[$currentType]["MODULE_TITLE"];
						$result["MESSAGE"] = $searchModules[$currentType]["NAME"];
						$result["DETAILS_DESCRIPTION"] = GetMessage('FILEINSPECTOR_CURRENT_ELEMENT_ID').$currentValues["LAST_ID"];
						$result['TYPE'] = 'PROGRESS';
					}
					else{

						if (count($currentValues["ERROR"]) > 0){
							COption::SetOptionString('alexkova.fileinspector', 'state', 'error');
							COption::SetOptionString('alexkova.fileinspector', 'last_error', serialize($currentValues["ERROR"]));
						}
						else{
							$result["DMESSAGE"] = self::scanNextModule($currentType);
						}
					}
				}
				else{
					$result["DMESSAGE"] = self::scanNextModule($currentType);
				}

				break;

			case "IBLOCK_ELEMENTS":

				if (self::checkSuccess($currentType)){

					$allCount = CAFIblockElementSearcher::getCount();
					$currentValues = CAFIblockElementSearcher::checkList(intval(COption::GetOptionString('alexkova.fileinspector', 'current_element_id')));

					COption::SetOptionString('alexkova.fileinspector', 'current_element_id', $currentValues["LAST_ID"]);
					COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', 'IBLOCK_ELEMENTS');

					if (count($currentValues["FIELDS"])>0 && count($currentValues["ERROR"]) <= 0){
						$result["PROGRESS_VALUE"] = $currentValues["LAST_ID"] / $allCount * 100;
						$result["DETAILS"] = $searchModules[$currentType]["MODULE_TITLE"];
						$result["MESSAGE"] = $searchModules[$currentType]["NAME"];
						$result['TYPE'] = 'PROGRESS';
					}
					else{

						if (count($currentValues["ERROR"]) > 0){
							COption::SetOptionString('alexkova.fileinspector', 'state', 'error');
							COption::SetOptionString('alexkova.fileinspector', 'last_error', serialize($currentValues["ERROR"]));
						}
						else{
							$result["DMESSAGE"] = self::scanNextModule($currentType);
						}
					}
				}
				else{
					$result["DMESSAGE"] = self::scanNextModule($currentType);
				}

				break;

			case "IBLOCK_SECTIONS":

				if (self::checkSuccess($currentType)){

					$allCount = CAFIblockElementSearcher::getSectionCount();
					$currentValues = CAFIblockElementSearcher::checkSectionList(intval(COption::GetOptionString('alexkova.fileinspector', 'current_section_id')));

					COption::SetOptionString('alexkova.fileinspector', 'current_section_id', $currentValues["LAST_ID"]);
					COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', 'IBLOCK_SECTIONS');

					if (count($currentValues["FIELDS"])>0 && count($currentValues["ERROR"]) <= 0){
						$result["PROGRESS_VALUE"] = $currentValues["LAST_ID"] / $allCount * 100;
						$result["DETAILS"] = $searchModules[$currentType]["MODULE_TITLE"];
						$result["MESSAGE"] = $searchModules[$currentType]["NAME"];
						$result['TYPE'] = 'PROGRESS';
					}
					else{

						if (count($currentValues["ERROR"]) > 0){
							COption::SetOptionString('alexkova.fileinspector', 'state', 'error');
							COption::SetOptionString('alexkova.fileinspector', 'last_error', serialize($currentValues["ERROR"]));
						}
						else{
							$result["DMESSAGE"] = self::scanNextModule($currentType);
						}
					}
				}
				else{
					$result["DMESSAGE"] = self::scanNextModule($currentType);
				}

				break;

			case "IBLOCK_FILE":

				if (self::checkSuccess($currentType)){

					$currentValues = CAFIBFile::checkFileList('iblock');

					if ($currentValues["RESULT"]["TSTATE"] == 'progress'){

						$result["DETAILS"] = $searchModules[$currentType]["MODULE_TITLE"];
						$result["MESSAGE"] = $searchModules[$currentType]["NAME"]."<br />"."Last file:".COption::GetOptionString('alexkova.fileinspector', 'current_iblock_file')
						."<br /> Total: ".COption::GetOptionString('alexkova.fileinspector', 'current_iblock_filecount')
						."<br /> Total Size (Mb): ".round(COption::GetOptionString('alexkova.fileinspector', 'current_iblock_filesize')/1024/1024);
						$result['TYPE'] = 'OK';

						COption::SetOptionString('alexkova.fileinspector', 'state', 'progress');
						COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', 'IBLOCK_FILE');



					}
					elseif ($currentValues["RESULT"]["TSTATE"] == 'success'){
						$result["DMESSAGE"] = self::scanNextModule($currentType);
					}
					else{
						COption::SetOptionString('alexkova.fileinspector', 'state', 'error');
						COption::SetOptionString('alexkova.fileinspector', 'last_error', serialize(array('IBLOCK_FILE Unknown error')));
					}
				}
				else{
					$result["DMESSAGE"] = self::scanNextModule($currentType);
				}

				break;

			case "B_FILE":

				if (self::checkSuccess($currentType)){

					$allCount = CAFIBFile::checkFileListFromDBCount();
					$currentValues = CAFIBFile::checkFileListFromDB(intval(COption::GetOptionString('alexkova.fileinspector', 'current_bfile_id')));

					COption::SetOptionString('alexkova.fileinspector', 'current_bfile_id', $currentValues["LAST_ID"]);
					COption::SetOptionString('alexkova.fileinspector', 'last_scan_operation', 'B_FILE');

					if (count($currentValues["FIELDS"])>0 && count($currentValues["ERROR"]) <= 0){
						$result["PROGRESS_VALUE"] = $currentValues["LAST_ID"] / $allCount * 100;
						$result["DETAILS"] = $searchModules[$currentType]["MODULE_TITLE"];
						$result["MESSAGE"] = $searchModules[$currentType]["NAME"];
						$result["DETAILS_DESCRIPTION"] = GetMessage('FILEINSPECTOR_CURRENT_ELEMENT_ID').$currentValues["LAST_ID"];
						$result['TYPE'] = 'PROGRESS';
					}
					else{

						if (count($currentValues["ERROR"]) > 0){
							COption::SetOptionString('alexkova.fileinspector', 'state', 'error');
							COption::SetOptionString('alexkova.fileinspector', 'last_error', serialize($currentValues["ERROR"]));
						}
						else{
							$result["DMESSAGE"] = self::scanNextModule($currentType);
						}
					}
				}
				else{
					$result["DMESSAGE"] = self::scanNextModule($currentType);
				}

				break;

			default:
				$result["state"] = 'success';
				COption::SetOptionString('alexkova.fileinspector', 'state', 'success');
				COption::SetOptionString('alexkova.fileinspector', 'date_success', time());
				$result["PROGRESS_VALUE"] = 100;
				break;
		}

		self::refreshActuality();

		return $result;
	}

	public static function drawStatistic($indata = array()){

		$state = self::getState();

		$stateDetails = GetMessage('FILEINSPECTOR_SEARCH_STATE_DETAILS');

		$message = array(
			"TYPE" => "OK",
			"MESSAGE" => GetMessage('FILEINSPECTOR_SEARCH_STATE'),
			"DETAILS" => $stateDetails[$state["LAST_OPERATION"]],
			"HTML" => true
		);

		if ($state["STATE"] == "success"){
			$message['MESSAGE'] = GetMessage('FILEINSPECTOR_SEARCH_SUCCESS');
			$message['DETAILS'] = GetMessage('FILEINSPECTOR_SEARCH_SUCCESS_DATE').ConvertTimeStamp($state['DATE_SUCCESS'],"FULL");
		}

		if ($state["STATE"] == 'error'){
			$message['TYPE'] = "";
			$message['MESSAGE'] = GetMessage('FILEINSPECTOR_SEARCH_ERROR');
			$message['DETAILS'] = implode('<br />',$state["LAST_ERROR"]);
		}

		if (is_array($indata["DMESSAGE"])){
			$message = $indata["DMESSAGE"];
		}

		CAdminMessage::ShowMessage($message);

	}

	public static function drawStep($result){

		$drawData = array(
			'TYPE' => $result["TYPE"],
			'MESSAGE' => $result["MESSAGE"],
			'DETAILS' => $result["DETAILS"].'#PROGRESS_BAR#'.
				'<span id="counter_field">'.$result["DETAILS_DESCRIPTION"].'</span>',
			'HTML' => true,
			'PROGRESS_TOTAL'=> 100,
			'PROGRESS_VALUE' => $result["PROGRESS_VALUE"]
		);

		if ($result["progress"] == 'start'){

		}

		if ($result["progress"] == 'progress'){
			$drawData["PROGRESS_VALUE"] = $result["PROGRESS_VALUE"];
		}

		if (strlen($result["MESSAGE"])>0){
			self::drawProgressBar($drawData);
		}
		else{
			self::drawStatistic($result);
		}

	}

	public static function drawProgressBar($data){

		CAdminMessage::ShowMessage(array(
			"TYPE" => $data['TYPE'],
			"MESSAGE" => $data['MESSAGE'],
			"DETAILS" => $data['DETAILS'],
			"HTML" => $data['HTML'],
			"PROGRESS_TOTAL" => $data['PROGRESS_TOTAL'],
			"PROGRESS_VALUE" => $data['PROGRESS_VALUE'],
		));

	}

	public static function showDocNotes(){
		echo BeginNote();
		echo GetMessage("FILEINSPECTOR_DOC_NOTES");
		echo EndNote();
	}
}
