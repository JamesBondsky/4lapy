<?
//include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/lib/property.php");
//include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/lib/inheritedproperty.php");
//include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lib/utsuser.php");

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Alexkova\Fileinspector\StatisticTable as STable;
//use Bitrix\Iblock\PropertyTable;
//use Bitrix\Main\UtmUserTable;

Loc::loadMessages(__FILE__);

class CAFITools
{




#$precision количество цифр после точки 1.23 MB


	function FBytes($bytes, $precision = 2) {

		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes?log($bytes):0)/log(1024));
		$pow = min($pow, count($units)-1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision).' '.$units[$pow];

	}

	function bin_strpos($s, $a)
	{
			if (function_exists('mb_orig_strpos'))
					return mb_orig_strpos($s, $a);
			return strpos($s, $a);
	}
        

	function ScanDirSize($path, $seance)
	{
		global $MARR;
		global $operationID;



		if (time() - $MARR[$seance]["START_TIME"] > 5)
		{

				if (!isset($MARR[$seance]['BREAK_POINT']))
						$MARR[$seance]['BREAK_POINT'] = $path;
				return;
		}



		if (isset($MARR[$seance]['SKIP_PATH']) && !isset($MARR[$seance]['FOUND'])) // ��������, ������� �� ������� ����
		{
				if (0 !== $this->bin_strpos($MARR[$seance]['SKIP_PATH'], dirname($path))) // ����������� ��� ��� ��� ����
						return;

				if ($MARR[$seance]['SKIP_PATH']==$path) // ���� ������, ���������� ������ �����
						$MARR[$seance]['FOUND'] = true;
		}

		if (is_dir($path)) // dir
		{
				$dir = opendir($path);
				while($item = readdir($dir))
				{
						if ($item == '.' || $item == '..')
								continue;

						$this->ScanDirSize($path.'/'.$item, $seance);
				}
				closedir($dir);
		}
		else // file
		{
				if (!isset($MARR[$seance]['SKIP_PATH']) || isset($MARR[$seance]['FOUND']))
				{
					// требуется вставить локальную проверку
					$checkFile = CAFICheckFile::checkFileByName($path, $seance);

					foreach($checkFile["FILES"] as $file){

						$newFields = $file;

						$newFields["DATE_MODIFY"] = \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields["UTM"]);;
						$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
						$newFields["BASE_ID"] = 0;

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
							$result["ERROR"] = $resultAdd->getErrorMessages();
						}

					}

					if (count($checkFile["FILES"])>1){
						$newFields = $checkFile["FILES"][0];

						$newFields["DATE_MODIFY"] = \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields["UTM"]);;
						$newFields["TIMESTAMP_X"] = new \Bitrix\Main\Type\DateTime();
						$newFields["BASE_ID"] = 0;

						$newFields["DEFFECT_CODE"] = $checkFile["MORE_DEFFECT"]["DEFFECT_CODE"];
						$newFields["DEFFECT_DETAIL"] = $checkFile["MORE_DEFFECT"]["DEFFECT_DETAIL"];


						$newFields["SEARCH_DETAIL"] = $checkFile["SEARCH_DETAIL"];

						unset ($newFields["EXTERNAL_ID"]);
						unset ($newFields["UTM"]);
						unset ($newFields["ID"]);

						$resultAdd = STable::add($newFields);

						if($resultAdd->isSuccess())
						{
							$id = $resultAdd->getId();
						}
						else
						{
							$MARR[$seance]["ERROR"] = $resultAdd->getErrorMessages();;
						}
					}



					$MARR[$seance]["FSIZE"] = $MARR[$seance]["FSIZE"] + filesize($path);
					$MARR[$seance]["FCOUNT"] = $MARR[$seance]["FCOUNT"] + 1;
					$MARR[$seance]["FILES"][] = $path;

				  //  sleep(1);
				}
		}

		return $MARR;
	}
        
	function ScannerBot($arFields){

		if (strlen($arFields["PATH"])>0)
		{
			global $MARR;

			if (!is_array($MARR) || !isset($MARR["START_TIME"]))
			{
				$MARR = array($arFields["SEANCE"]=>array("START_TIME"=>time()));
				if ($arFields["FCOUNT"]>0) $MARR[$arFields["SEANCE"]]["FCOUNT"] = $arFields["FCOUNT"];
				if ($arFields["FSIZE"]>0) $MARR[$arFields["SEANCE"]]["FSIZE"] = $arFields["FSIZE"];
				if (strlen($arFields["BREAK_POINT"])>0) $MARR[$arFields["SEANCE"]]["SKIP_PATH"] = $arFields["BREAK_POINT"];
			}
			$arFields["PATH"] = str_replace("//", "/", $arFields["PATH"]);
			$this->ScanDirSize($arFields["PATH"], $arFields["SEANCE"]);

			if (isset($MARR[$arFields["SEANCE"]]["BREAK_POINT"]) && strlen($MARR[$arFields["SEANCE"]]["BREAK_POINT"])>0)
				$MARR[$arFields["SEANCE"]]["TSTATE"] = "progress";
			else
				$MARR[$arFields["SEANCE"]]["TSTATE"] = "success";
		}

		$MARR[$arFields["SEANCE"]]["DEBUG"] = $arFields;

		return $MARR;
	}
}
?>
