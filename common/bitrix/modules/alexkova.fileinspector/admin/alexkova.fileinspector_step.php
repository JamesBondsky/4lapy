<?
// admin initialization
define("MODULE_NAME", "alexkova.fileinspector");

//$APPLICATION->RestartBuffer();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

set_time_limit(0);
global $operationID;

if (!CModule::IncludeModule(MODULE_NAME)) return;

IncludeModuleLangFile(__FILE__);
?>
<?//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");?>

<?
if (intval($_REQUEST["stopscan"]) == 1){
	COption::SetOptionString('alexkova.fileinspector', 'state', 'stop');
	CAFISearcher::drawStatistic();
}
else{
	$state = CAFISearcher::getState();
	if ($state["STATE"] == 'stop'){
		COption::SetOptionString('alexkova.fileinspector', 'state', 'progress');
	}

	if ($state["STATE"] == 'progress' || $state["STATE"] == 'start'){
		CAFISearcher::drawStep(CAFISearcher::nextStep());
	}

	if ($state["STATE"] == 'error'){
		CAFISearcher::drawStatistic();
	}
}

$state = CAFISearcher::getState();
echo '<div id="state-indicator" style="display:none">'.$state["STATE"].'</div>';
?>
<?//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>