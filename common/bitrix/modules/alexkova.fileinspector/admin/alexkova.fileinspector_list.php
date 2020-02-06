<?php

// admin initialization
define("ADMIN_MODULE_NAME", "alexkova.fileinspector");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alexkova.fileinspector/lang/".LANGUAGE_ID."/classes/general/checkfile.php");

IncludeModuleLangFile(__FILE__);

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule('iblock'))
{
	return;
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

use Alexkova\Fileinspector\Tools as STools;
use Alexkova\Fileinspector\StatisticTable as STable;
//use Alexkova\Fileinspector\FileTable as STable;

$APPLICATION->SetTitle(GetMessage('FILEINSPECTOR_ADMIN_LIST_TITLE'));

$entity_table_name = STable::getTableName();

$sTableID = $entity_table_name;
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = Array(
	"find_name",
	"find_operation_id",
	"find_error_code",
);
function CheckFilter($FilterArr)
{
	foreach($FilterArr as $f)
		global $$f;

	return true;

}


$arFilter = Array();
$lAdmin->InitFilter($arFilterFields);
InitSorting();

if(CheckFilter($arFilterFields))
{
	if (strlen(trim($find_name))>0){
		$arFilter["FILE_NAME"] = '%'.$find_name.'%';
	}

	if (strlen(trim($find_operation_id))>0){
		$arFilter["OPERATION_ID"] = $find_operation_id;
	}

	if (strlen(trim($find_error_code))>0){
		$arFilter["DEFFECT_CODE"] = $find_error_code;
	}
}


$arFields = STable::getMap();

$arHeaders = array();

foreach ($arFields as $cell=>$value){
	$arHeaders[] = array(
		'id' => $cell,
		'content' => $value["title"],
		'sort' => $cell,
		'default' => $value["def"]=="Y" ? true : false,
	);
}

$lAdmin->AddHeaders($arHeaders);

if (!in_array($by, $lAdmin->GetVisibleHeaderColumns(), true))
{
	$by = 'ID';
}

$rsData = STable::getList(array(
	"select" => $lAdmin->GetVisibleHeaderColumns(),
	"order" => array($by => strtoupper($order)),
	'filter' => $arFilter
));

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

$aMenu = array(
		/*array(
			"TEXT"    => GetMessage('HLBLOCK_ADMIN_ROWS_ADD_NEW_BUTTON'),
			"TITLE"    => GetMessage('HLBLOCK_ADMIN_ROWS_ADD_NEW_BUTTON'),
			"LINK"    => "highloadblock_row_edit.php?ENTITY_ID=".intval($_REQUEST['ENTITY_ID'])."&lang=".LANGUAGE_ID,
			"ICON"    => "btn_new",
		),
		array(
			"TEXT"    => GetMessage('HLBLOCK_ADMIN_ROWS_EDIT_ENTITY'),
			"TITLE"    => GetMessage('HLBLOCK_ADMIN_ROWS_EDIT_ENTITY'),
			"LINK"    => "highloadblock_entity_edit.php?ID=".$hlblock['ID']."&lang=".LANGUAGE_ID,
			"ICON"    => "btn_edit",
		)*/
	);

$context = new CAdminContextMenu($aMenu);

$lAdmin->NavText($rsData->GetNavPrint("PAGES"));
while($arRes = $rsData->NavNext(true, "f_"))
{
	$tmpSearchDetail = unserialize($arRes["SEARCH_DETAIL"]);
	$tmpSearchStr = '';
	$prefix = '';


	foreach($tmpSearchDetail as $cell => $val){

		
		if ($val["TYPE"] == 'B_FILE'){
			$tmpSearchStr .= $prefix.GetMessage('FILEINSPECTOR_B_FILE_EXIST')." ".$val["ID"];
			if (isset($val["OBJECT_TYPE"])){
				$msTmp = GetMessage('FILEINSPECTOR_B_FILE_OBJECT_FOUND');
				$msTmp = str_replace('#OBJECT_ID#', $val["OBJECT_ID"],$msTmp);
				$msTmp = str_replace('#OBJECT_TYPE#', $val["OBJECT_TYPE"],$msTmp);
				$tmpSearchStr .= $msTmp;
				$prefix = "<br><br>";
			}
			elseif(isset($val["MODULE_ID"])){
				$msTmp = GetMessage('FILEINSPECTOR_B_FILE_MODULE_FOUND');
				$msTmp = str_replace('#MODULE_ID#', $val["MODULE_ID"],$msTmp);
				$msTmp = str_replace('#PATH#', $val["PATH"],$msTmp);
				$tmpSearchStr .= $msTmp;
				$prefix = "<br><br>";
			}
			else{
				$tmpSearchStr .= GetMessage('FILEINSPECTOR_B_FILE_FOUND').' '.$val["ABS_PATH"];
			}
			$prefix = "<br><br>";
		}

		if ($val["TYPE"] == 'IBLOCK'){
			$msTmp = GetMessage('FILEINSPECTOR_IBLOCK_FOUND');
			$msTmp = str_replace('#IBLOCK_ID#', $val["ID"],$msTmp);
			$msTmp = str_replace('#NAME#', $val["NAME"],$msTmp);
			$tmpSearchStr .= $prefix.GetMessage('FILEINSPECTOR_IBLOCK_EXIST')." ".$val["PATH"].' '.$msTmp;
			$prefix = "<br><br>";
		}

		if ($val["TYPE"] == 'IBLOCK_ELEMENT'){
			$msTmp = GetMessage('FILEINSPECTOR_IBLOCK_ELEMENT_FOUND');
			$msTmp = str_replace('#IBLOCK_ID#', $val["IBLOCK_ID"],$msTmp);
			$msTmp = str_replace('#ID#', $val["ID"],$msTmp);
			$msTmp = str_replace('#NAME#', $val["NAME"],$msTmp);
			$tmpSearchStr .= $prefix.GetMessage('FILEINSPECTOR_IBLOCK_ELEMENT_EXIST')." ".$val["PATH"].' '.$msTmp;
			$prefix = "<br><br>";
		}

		if ($val["TYPE"] == 'IBLOCK_SECTION'){
			$msTmp = GetMessage('FILEINSPECTOR_IBLOCK_SECTION_FOUND');
			$msTmp = str_replace('#IBLOCK_ID#', $val["IBLOCK_ID"],$msTmp);
			$msTmp = str_replace('#ID#', $val["ID"],$msTmp);
			$msTmp = str_replace('#NAME#', $val["NAME"],$msTmp);
			$tmpSearchStr .= $prefix.GetMessage('FILEINSPECTOR_IBLOCK_SECTION_EXIST')." ".$val["PATH"].' '.$msTmp;
			$prefix = "<br><br>";
		}

		if ($val["TYPE"] == 'IBLOCK_PROPERTY_1' || $val["TYPE"] == 'IBLOCK_PROPERTY_2'){
			$msTmp = GetMessage('FILEINSPECTOR_IBLOCK_PROPERTY_FOUND');
			$msTmp = str_replace('#ID#', $val["ID"],$msTmp);
			$msTmp = str_replace('#PID#', $val["PID"],$msTmp);
			$tmpSearchStr .= $prefix.GetMessage('FILEINSPECTOR_IBLOCK_PROPERTY_EXIST').' '.$msTmp;
			$prefix = "<br><br>";
		}
	}

	if (strlen($tmpSearchStr) >0 ) $arRes["SEARCH_DETAIL"] = $tmpSearchStr;

	$row = $lAdmin->AddRow($f_ID, $arRes);
	$row->AddViewField("SEARCH_DETAIL", $tmpSearchStr);


	//echo "<pre>"; print_r(unserialize($arRes["SEARCH_DETAIL"])); echo "</pre>";

	//$USER_FIELD_MANAGER->AddUserFields('FINSPECTOR_', $arRes, $row);

	$can_edit = true;

	$arActions = Array();

	/*$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"ACTION" => $lAdmin->ActionRedirect("highloadblock_row_edit.php?ENTITY_ID=".$hlblock['ID'].'&ID='.$f_ID),
		"DEFAULT" => true
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('HLBLOCK_ADMIN_DELETE_ROW_CONFIRM')."')) ".
			$lAdmin->ActionRedirect("highloadblock_row_edit.php?action=delete&ENTITY_ID=".$hlblock['ID'].'&ID='.$f_ID.'&'.bitrix_sessid_get())
	);

	$row->AddActions($arActions);*/

	// deny group operations (hide checkboxes)
	//$row->pList->bCanBeEdited = true;
}

$lAdmin->AddAdminContextMenu(array());


$lAdmin->CheckListMode();


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
	<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
		<input type="hidden" name="lang" value="<?echo LANG?>">
		<?
		$oFilter = new CAdminFilter(
			$sTableID."_filter",
			array(
				GetMessage("FILEINSPECTOR_LIST_NAME"),
				GetMessage("FILEINSPECTOR_LIST_OPERATION_ID"),
				GetMessage("FILEINSPECTOR_LIST_DEFFECT_CODE")
			)
		);

		$oFilter->Begin();

		$dCode = GetMessage('DEFFECT_CODE');

		?>

		<tr>
			<td><?echo GetMessage("FILEINSPECTOR_LIST_NAME")?>:</td>
			<td><input type="text" name="find_name" size="47" value="<?echo htmlspecialcharsbx($find_name)?>"></td>
		</tr>
		<tr>
			<td><?echo GetMessage("FILEINSPECTOR_LIST_OPERATION_ID")?>:</td>
			<td>
				<select name="find_operation_id">
					<option value="" <?if(strlen($find_operation_id) == 0 || true) echo 'selected="selected"'?>><?=GetMessage('FILEINSPECTOR_LIST_OPERATION_ID_NULL')?></option>
					<?foreach (CAFISearcher::getSM() as $cell=>$val):?>
						<option value="<?=$cell?>" <?if(strlen($find_operation_id) == $cell) echo 'selected="selected"'?>><?="[".$cell."] ".$val["NAME"]?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("FILEINSPECTOR_LIST_DEFFECT_CODE")?>:</td>
			<td>
				<select name="find_error_code">
					<option value="" <?if(strlen($find_error_code) == 0 || true) echo 'selected="selected"'?>><?=GetMessage('FILEINSPECTOR_LIST_DEFFECT_CODE_NULL')?></option>
					<?foreach ($dCode as $cell=>$val):?>
						<?if ($val == '0'):?>
							<option value="<?=$cell?>" <?if(strlen($find_error_code) == $cell) echo 'selected="selected"'?>><?="[".$cell."] ".$val?></option>
						<?endif;?>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		<?
		$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
		$oFilter->End();
		?>
	</form>
<?
$lAdmin->DisplayList();
?>

<?
CAFISearcher::showDocNotes();
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>