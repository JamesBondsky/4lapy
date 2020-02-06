<?
use Alexkova\Fileinspector\StatisticTable as STable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
Loc::loadMessages(__FILE__);


class CAFICheckFile{

	public static function checkFileByName($path, $type){

		$result = array(
			"DEFFECT_CODE" => '100',
			"DEFFECT_DETAIL" => 'unknoun',
		);

		$tpath = str_replace('//', '/', $path);

		$delimiter = COption::GetOptionString('main', 'upload_dir');

		$arPath = explode($delimiter, $tpath);
		$arPath = explode("/", $arPath[1]);

		$subdir = "";
		$prefix = "";
		for($i=0; $i<count($arPath)-1; $i++){
			if (strlen($arPath[$i]) >0){
				$subdir .= $prefix.$arPath[$i];
				$prefix = "/";
			}
		}

		$filename = $arPath[count($arPath)-1];



		global $DB;

		$strSQL = "SELECT *, UNIX_TIMESTAMP(TIMESTAMP_X) AS UTM FROM b_file WHERE
		SUBDIR = '$subdir' AND FILE_NAME = '$filename'
		";

		//echo "455<pre>"; print_r($strSQL); echo "</pre>";


		$res = $DB->Query($strSQL, false, __FILE__);

		$arFinds = array();

		while ($arFields = $res->Fetch()){

			$arFinds[] = $arFields;
		}

		if (count($arFinds)>0){

			$searchResult = array();
			foreach ($arFinds as $file){
				$searchResult[] = array(
						"TYPE" => 'B_FILE',
						"ID" => $file["ID"],
						"MODULE_ID" => $file["MODULE_ID"],
						"FILE_TYPE" => $type,
						"PATH" => $path
				);
			}

			$result = array(
				"DEFFECT_CODE" => '0',
				"DEFFECT_DETAIL" => '',
				"FILES" => $arFinds,
				'SEARCH_DETAIL' => serialize($searchResult)
			);
		}
		else{

			$deffectCode = GetMessage('DEFFECT_CODE');

			$fileInfoArray = CFile::MakeFileArray($path);
			$fileInfo = array();

			$fileInfo["SUBDIR"] = $subdir;
			$fileInfo["FILE_NAME"] = $filename;
			$fileInfo["FILE_SIZE"] = $fileInfoArray["size"];
			$fileInfo["CONTENT_TYPE"] = $fileInfoArray["type"];

			$result = array(
				"FILES" => array(
					$fileInfo
				),
				"DEFFECT_CODE" => '101005',
				"DEFFECT_DETAIL" => $deffectCode['101005'].'OBJECT_ID: '.$path,
			);
		}

		if (count($arFinds) > 1){
			$result["MORE_DEFFECT"] = array(
				"DEFFECT_CODE" => '101006',
				"DEFFECT_DETAIL" => $deffectCode['101006'].'OBJECT_ID: '.$path,
			);
		}

		return $result;

	}

	public function checkFileByID($ID, $OBJECT_ID, $OBJECT_TYPE){

		$result = array(
			"DEFFECT_CODE" => '100',
			"DEFFECT_DETAIL" => 'unknoun',
		);

		$file = CFile::GetFileArray($ID);

		if (is_array($file)){
			$result = array(
				"DEFFECT_CODE" => '0',
				"DEFFECT_DETAIL" => '',
				"FILE" => $file,
				'SEARCH_DETAIL' => serialize(array(
					array(
						"TYPE" => 'B_FILE',
						"ID" => $file["ID"],
						"OBJECT_TYPE" => $OBJECT_TYPE,
						"OBJECT_ID" => $OBJECT_ID,
					)
				))
			);
		}
		else{

			$deffectCode = GetMessage('DEFFECT_CODE');

			$result = array(
				"DEFFECT_CODE" => '101004',
				"DEFFECT_DETAIL" => $deffectCode['101004'].'OBJECT_ID: '.$ELEMENT_ID,
			);
		};

		return $result;

	}

	public static function analizeIblockBFile($ID){

		$result = array(
			'FIND' => false
		);

		global $DB, $PROPS;

		// scanning iblock
		$strSQL = "SELECT * FROM b_iblock WHERE PICTURE = ".$ID;
		$res = $DB->Query($strSQL, false, __LINE__);

		while ($arFields = $res->Fetch()){
			$result["FIND"] = true;
			$result["DETAILS"][] = array(
				"TYPE" => "IBLOCK",
				"PATH" => $arFields["PICTURE"],
				"ID" => $arFields["ID"],
				"NAME" => $arFields["NAME"],
			);
		}

		// scanning iblock elements
		$strSQL = "SELECT * FROM b_iblock_element WHERE PREVIEW_PICTURE = ".$ID." OR DETAIL_PICTURE = ".$ID;
		$res = $DB->Query($strSQL, false, __LINE__);

		while ($arFields = $res->Fetch()){
			$result["FIND"] = true;
			$result["DETAILS"][] = array(
				"TYPE" => "IBLOCK_ELEMENT",
				"PATH" => $arFields["PREVIEW_PICTURE"] == $ID ? "PREVIEW_PICTURE" : "DETAIL_PICTURE",
				"ID" => $arFields["ID"],
				"IBLOCK_ID" => $arFields["IBLOCK_ID"],
				"NAME" => $arFields["NAME"],
			);
		}

		// scanning iblock sections

		$strSQL = "SELECT * FROM b_iblock_section WHERE PICTURE = ".$ID." OR DETAIL_PICTURE = ".$ID;
		$res = $DB->Query($strSQL, false, __LINE__);

		while ($arFields = $res->Fetch()){
			$result["FIND"] = true;
			$result["DETAILS"][] = array(
				"TYPE" => "IBLOCK_SECTION",
				"PATH" => $arFields["PICTURE"] == $ID ? "PICTURE" : "DETAIL_PICTURE",
				"ID" => $arFields["ID"],
				"IBLOCK_ID" => $arFields["IBLOCK_ID"],
				"NAME" => $arFields["NAME"],
			);
		}

		// scanning iblock properties


		if (!is_array($PROPS)) CAFIblockSearcher::getAllFileProps();

		// analize iblock properties version 1
		if (count($PROPS[1])>0){

			$str_property_where = '';
			$str_property_where_prefix = '';

			foreach($PROPS["ALL"]["1"] as $cell=>$val){
				$str_property_where .= $str_property_where_prefix.$val;
				$str_property_where_prefix = ', ';
			}
			if (strlen($str_property_where)>0){
				$str_propert_where = ' AND IBLOCK_PROPERTY_ID IN ('.$str_property_where.")";
			}

			$strSQL = "
				select
				IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, ID
				from
				b_iblock_element_property
				where VALUE = $ID
				$str_propert_where
			";


			$res = $DB->Query($strSQL, false, __LINE__);

			while ($arFields = $res->Fetch()){
				$result["FIND"] = true;
				$result["DETAILS"][] = array(
					"TYPE" => "IBLOCK_PROPERTY_1",
					"ID" => $arFields["IBLOCK_ELEMENT_ID"],
					"PID" => $arFields["IBLOCK_PROPERTY_ID"]
				);
			}
		}

		// analize single properties version 2
		if (count($PROPS[2])>0){

			// analize multiple iblock properties version 2
			if (count($PROPS[2][0])>0){

				$prefixBlock = "";
				$strSQL = "";

				foreach($PROPS[2][0] as $iblock=>$prop_id){

					foreach ($prop_id as $val){
						$pid = $val["ID"];
						$strSQL .= $prefixBlock."
						(
						SELECT IBLOCK_ELEMENT_ID, $pid AS IBLOCK_PROPERTY_ID FROM b_iblock_element_prop_s$iblock
						WHERE PROPERTY_$pid = $ID
						)
						";

						$prefixBlock = " UNION ";
					}


				}

				//echo "<pre>"; print_r($strSQL); echo "</pre>"; die();

				$res = $DB->Query($strSQL, false, __LINE__);

				while ($arFields = $res->Fetch()){
					$result["FIND"] = true;
					$result["DETAILS"][] = array(
						"TYPE" => "IBLOCK_PROPERTY_2",
						"ID" => $arFields["IBLOCK_ELEMENT_ID"],
						"PID" => $arFields["IBLOCK_PROPERTY_ID"],
						"IBLOCK_ID"=>$iblock
					);
				}

			}

			// analize multiple iblock properties version 2
			if (count($PROPS[2][1])>0){

				$prefixBlock = "";
				$strSQL = "";

				foreach($PROPS[2][1] as $iblock=>$prop_id){

					$prefixWhere = "";
					$pid = "";
					foreach ($prop_id as $val){
						$pid .= $prefixWhere.$val["ID"];
						$prefixWhere = ",";
					}

					$strSQL .= $prefixBlock."
						(
						SELECT IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID FROM b_iblock_element_prop_m$iblock
						WHERE IBLOCK_PROPERTY_ID IN ($pid) AND VALUE = $ID
						)
						";

					$prefixBlock = " UNION ";

				}

				$res = $DB->Query($strSQL, false, __LINE__);

				while ($arFields = $res->Fetch()){
					$result["FIND"] = true;
					$result["DETAILS"][] = array(
						"TYPE" => "IBLOCK_PROPERTY_2",
						"ID" => $arFields["IBLOCK_ELEMENT_ID"],
						"PID" => $arFields["IBLOCK_PROPERTY_ID"],
						"IBLOCK_ID"=>$iblock
					);
				}

			}
		}


		// return full scan result
		return $result;
	}

	public static function checkIblockBFile($ID){

		$result = array(
			"DEFFECT_CODE" => '100',
			"DEFFECT_DETAIL" => 'unknoun',
		);

		$scanResult = self::analizeIblockBFile($ID);

		if ($scanResult['FIND']){

			$result = array(
				"DEFFECT_CODE" => '0',
				"DEFFECT_DETAIL" => '',
				"SEARCH_DETAIL" => serialize($scanResult['DETAILS'])
			);
		}
		else{

			$deffectCode = GetMessage('DEFFECT_CODE');

			$result = array(
				"DEFFECT_CODE" => '101001',
				"DEFFECT_DETAIL" => $deffectCode['101001'],
			);
		}

		return $result;
	}

}