<?php
// admin initialization
define("ADMIN_MODULE_NAME", "alexkova.fileinspector");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/alexkova.fileinspector/lang/".LANGUAGE_ID."/classes/general/searcher.php");

IncludeModuleLangFile(__FILE__);
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}


function AjaxRefreshScanning($mode){

	global $APPLICATION;

	switch ($mode){

		case 'stop' :
			COption::SetOptionString('alexkova.fileinspector', 'state', 'stop');
		break;

		case 'next' :
			COption::SetOptionString('alexkova.fileinspector', 'state', 'progress');
		break;

		case 'start':

			$searchModules = CAFISearcher::getSM();

			foreach($searchModules as $cell => $arOption)
			{
				$type = strtolower("scan_".$cell);
				$val= isset($_REQUEST[$type]) ? $_REQUEST[$type] : "N";

				COption::SetOptionString("alexkova.fileinspector", $type, $val);
				COption::SetOptionString('alexkova.fileinspector', 'state', 'start');

			};
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID);
		break;

	}

}


if($REQUEST_METHOD=="POST" && strlen($Stop.$Next.$Start)>0 && check_bitrix_sessid()){

	$APPLICATION->RestartBuffer();

	if (strlen($Stop)>0){
		AjaxRefreshScanning('stop');
	}

	if (strlen($Next)>0){
		AjaxRefreshScanning('stop');
	}

	if (strlen($Start) > 0){
		AjaxRefreshScanning('start');
	}

	die();
}

$state = CAFISearcher::getState();
$searchModules = CAFISearcher::getSM();

if ($state["STATE"] == 'progress'){
	COption::SetOptionString('alexkova.fileinspector', 'state', 'stop');
	$state = CAFISearcher::getState();
}

$txtTitle = GetMessage('FILEINSPECTOR_ADMIN_STATECONTROL_TITLE');

$stateVAriants = array(
	'START' => array(
		'state' => true,
		'name' => 'Start',
		'type' => 'submit',
		'display' => true,
		'class' => 'adm-btn-save',
		'title'=>GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START')
	),
	'REFRESH' => array(
		'state' => false,
		'name' => 'Next',
		'display' => false,
		'type' => 'button',
		'title'=>GetMessage('FILEINSPECTOR_ADMIN_SCANNER_NEXT'),
		'onclick'=>'setNextStep();'
	),
	'CONTINUE' => array(
		'state' => true,
		'type' => 'button',
		'display' => false,
		'name' => 'Cont',
		'title'=>GetMessage('FILEINSPECTOR_ADMIN_SCANNER_NEXT'),
		'onclick'=>'stopScanner = 0; setNextStep();'
	),
	'STOP' => array(
		'state' => false,
		'type' => 'button',
		'display' => false,
		'name' => 'Stop',
		'title'=>GetMessage('FILEINSPECTOR_ADMIN_SCANNER_STOP'),
		'onclick'=>'stopScanner = 1; setNextStep();'
	),
);

switch ($state["STATE"]){

	case 'start':

		$stateControl = array(
			"TITLE" =>$txtTitle["START"],
			"DISPLAY_PANEL" =>true
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["START"]["title"] = GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START_NEW');
		$stateVAriants["REFRESH"]["state"] = true;
		$stateVAriants["STOP"]["state"] = true;
		$stateVAriants["STOP"]["display"] = true;

	break;

	case 'stop':

		$stateControl = array(
			"TITLE" =>$txtTitle["STOP"],
			"DISPLAY_PANEL" =>true
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["START"]["display"] = true;
		$stateVAriants["START"]["title"] = GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START_NEW');
		$stateVAriants["REFRESH"]["state"] = true;
		$stateVAriants["REFRESH"]["display"] = true;
		$stateVAriants["STOP"]["state"] = false;
		$stateVAriants["STOP"]["display"] = false;

		break;

	case 'error':

		$stateControl = array(
			"TITLE" =>$txtTitle["ERROR"],
			"DISPLAY_PANEL" =>true
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["START"]["display"] = true;
		$stateVAriants["START"]["title"] = GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START_NEW');
		$stateVAriants["REFRESH"]["state"] = false;
		$stateVAriants["REFRESH"]["display"] = false;
		$stateVAriants["STOP"]["state"] = false;
		$stateVAriants["STOP"]["display"] = false;

		break;

	case 'progress':

		$stateControl = array(
			"TITLE" =>$txtTitle["PROGRESS"],
			"DISPLAY_PANEL" =>true
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["START"]["title"] = GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START_NEW');
		$stateVAriants["REFRESH"]["state"] = false;
		$stateVAriants["REFRESH"]["display"] = false;
		$stateVAriants["STOP"]["display"] = true;
		$stateVAriants["STOP"]["state"] = true;

		break;

	case 'success':

		$stateControl = array(
			"TITLE" =>$txtTitle["SUCCESS"],
			"DISPLAY_PANEL" =>true
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["REFRESH"]["state"] = false;
		$stateVAriants["REFRESH"]["display"] = false;
		$stateVAriants["STOP"]["state"] = false;
		$stateVAriants["STOP"]["display"] = false;

		$aMenu = array(
			array(
				"TEXT"	=> GetMessage('FILEINSPECTOR_ADMIN_LISTPAGE_TITLE'),
				"LINK"	=> "/bitrix/admin/alexkova.fileinspector_list.php?lang=".LANGUAGE_ID,
				"TITLE"	=> GetMessage('FILEINSPECTOR_ADMIN_LISTPAGE_TITLE'),
				"ICON"	=> "btn_list"
			)
		);
		break;

	default:

		$stateControl = array(
			"TITLE" =>$txtTitle["DEFAULT"],
			"DISPLAY_PANEL" =>false
		);

		$stateVAriants["START"]["state"] = true;
		$stateVAriants["REFRESH"]["state"] = false;
		$stateVAriants["REFRESH"]["display"] = false;
		$stateVAriants["STOP"]["state"] = false;
		$stateVAriants["STOP"]["display"] = false;

		break;
}



$APPLICATION->SetTitle(GetMessage('FILEINSPECTOR_ADMIN_SCANNER_TITLE'));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
?>

<div id="state-information">
	<div id="state-indicator"><?=CAFISearcher::drawStatistic()?></div>
</div>
<div id="state-detail" style="display:none"></div>

<?
if (is_array($aMenu)){
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}


$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => GetMessage("FILEINSPECTOR_ADMIN_SCANNER_TAB"),
		"ICON" => "af_settings",
		"TITLE" => GetMessage("FILEINSPECTOR_ADMIN_SCANNER_TAB_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?echo LANGUAGE_ID?>">
<?$tabControl->BeginNextTab();?>
<?
foreach($searchModules as $cell => $arOption):
	$type = strtolower("scan_".$cell);
	$val = COption::GetOptionString("alexkova.fileinspector", $type, "Y");
	?>
	<tr>
		<td width="40%" nowrap>
			<label for="<?=$type?>"><?=$arOption["NAME"]?></label>
		<td width="60%">
			<input type="checkbox" id="<?=$type?>" name="<?=$type?>" value="Y"<?if($val=="Y")echo" checked";?>
				   <?if ($stateControl == "N") echo "disabled"?>>
		</td>
	</tr>

<?endforeach?>

	<!--<tr class="heading">
		<td colspan="2"><?=GetMessage('FILEINSPECTOR_ADMIN_RESTART_SCANNER')?></td>
	</tr>
	<tr>
		<td width="40%" nowrap>
			<label for="restart_scanner"><?=GetMessage('FILEINSPECTOR_ADMIN_RESTART_SCANNER_LABEL')?></label>
		<td width="60%">
			<input type="checkbox" id="restart_scanner" name="restart_scanner" value="Y" />
		</td>
	</tr>-->

<?$tabControl->Buttons();?>

	<?foreach($stateVAriants as $cell=>$val):?>
		<input type="<?=$val["type"]?>"
			   name="<?=$val["name"]?>"
			   id="<?='b'.$val["name"]?>"
			   value="<?=$val["title"]?>"
			   title="<?=$val["title"]?>"
			   class="<?=$val["class"]?>"
			<?if (!$val["state"]) echo "disabled"?>
			<?if ($val["onclick"]) echo "onclick='".$val["onclick"]."'"?>
			<?if (!$val["display"]) echo "style='display: none'";?>
		>
	<?endforeach;?>

<?=bitrix_sessid_post();?>

<?$tabControl->End();?>
</form>

<?


CJSCore::Init('jquery');
?>

<script>

	var stopScanner = 0;

	function setNextStep(){

		//alert(17);

		ShowWaitWindow();
		$.ajax({
			url: 'alexkova.fileinspector_step.php?tmi='+Math.random()+'&stopscan='+stopScanner,
			success: function(data){

				$('#state-information').html(data);
				$('#state-information').css('display', 'block');

				//console.log(data);

				indicator = $('#state-indicator').html();
				CloseWaitWindow();

				if (indicator == 'start' || indicator == 'progress'){

					$('#bStop').css('display', 'inline');
					$('#bStop').attr('enabled', 'enabled');
					$('#bCont').css('display', 'none');

					setTimeout('setNextStep()', 200);


				}

				if (indicator == 'stop'){
					$('#bCont').css('display', 'inline');
					$('#bStop').css('display', 'none');
					//$('#bCont').css('disabled', '');
				}

				if (indicator == 'error'){
					$('#bCont').css('display', 'none');
					$('#bStop').css('display', 'none');
					//$('#bCont').css('disabled', '');
				}

				if (indicator == 'success'){
					$('#bCont').css('display', 'none');
					$('#bStop').css('display', 'none');
					//$('#bCont').css('disabled', '');
				}
			}
		});
	}

	<?if ($state["STATE"] == "start" || $state["STATE"] == "progress"):?>
		setNextStep();
	<?endif;?>

</script>

<?
CAFISearcher::showDocNotes();

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>