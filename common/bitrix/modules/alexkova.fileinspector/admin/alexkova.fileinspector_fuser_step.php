<?
// admin initialization
define("MODULE_NAME", "alexkova.fileinspector");

//$APPLICATION->RestartBuffer();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);



if (CModule::IncludeModule(MODULE_NAME) && CModule::IncludeModule('sale')){

	set_time_limit(0);
	$currentNS_all = intval($_REQUEST["NS"]);

	$stat = CIFSaleUser::getStat();
	if ($stat['CHECK_REFULL'] && $stat['CHECK_REFULL']>0){
		$startTime = time();
		while (time()-$startTime < 3){
			CSaleUser::DeleteOldAgent();
		}
	}
	$stat = CIFSaleUser::getStat();
	if ($stat['CHECK_REFULL'] && $stat['CHECK_REFULL']>0){
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"MESSAGE" => GetMessage('FILEINSPECTOR_FUSER_CLEAN_PROGRESS'),
			'DETAILS' => '#PROGRESS_BAR#'.
				'<span id="counter_field">'.GetMessage("FILEINSPECTOR_END_CNT").$stat["CNT"].'</span>',
			"HTML" => "Y",
			"PROGRESS_TOTAL" => $currentNS_all,
			"PROGRESS_VALUE" => $currentNS_all-$stat["CNT"],
		));
		echo "<div style='display:none' id='state-indicator'>progress</div>";
		echo "<div style='display:none' id='all-cnt'>".$currentNS_all."</div>";
	}
	else{
		CAdminMessage::ShowMessage(array(
			"TYPE" => "OK",
			"MESSAGE" => GetMessage('FILEINSPECTOR_FUSER_CLEAN_END'),
			"HTML" => "Y",
		));
		echo "<div style='display:none' id='state-indicator'>success</div>";
	}
}


?>
<?//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>