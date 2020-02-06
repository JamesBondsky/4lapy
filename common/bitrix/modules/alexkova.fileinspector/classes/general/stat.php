<?php
/**
 * Created by PhpStorm.
 * User: kova
 * Date: 10.07.14
 * Time: 22:35
 */

class CAFIStat{

	public  static  function getStat(){

		global $DB;

		$result = array();

		$strSQL = "
				SELECT COUNT(ID) AS CNT, SUM(FILE_SIZE) as SIZE, DEFFECT_CODE from alexkova_fileinspector_statistic
		GROUP BY DEFFECT_CODE ORDER BY DEFFECT_CODE
		";

		$res = $DB->Query($strSQL, false, __FILE__);

		while ($arFields = $res->Fetch()){
			$result["ITEMS"][] = $arFields;
		}

		return $result;


	}

}