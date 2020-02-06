<?php
// admin initialization
define("ADMIN_MODULE_NAME", "alexkova.fileinspector");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(GetMessage('FILEINSPECTOR_ADMIN_STAT_TITLE'));
?>

<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

$stat = CIFSaleUser::getStat();?>

<div id="state-information">

<?if (count($stat["ERRORS"]) <=0 && !$stat['CHECK_REFULL']){
	CAdminMessage::ShowMessage(array(
		"TYPE" => "OK",
		"MESSAGE" => GetMessage('FILEINSPECTOR_FUSER_OK'),
		"HTML" => "Y",
	));
}
elseif(count($stat["ERRORS"])>0){
	CAdminMessage::ShowMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => implode('<br />', $stat["ERRORS"]),
		"HTML" => "Y",
	));
}
?>
	<div id="state-indicator"></div>
</div><div id="state-detail" style="display:none"></div>
<?
//$stat['CHECK_REFULL'] = true;
//$stat["CNT"] = 3888;

if ($stat['CHECK_REFULL']):
?>
	<?

	$NS = array();

	$NS["ALL_CNT"] = $stat["CNT"];

	$stateVAriants = array(
		'START' => array(
			'state' => true,
			'name' => 'Start',
			'type' => 'button',
			'display' => true,
			'class' => 'adm-btn-save',
			'onclick' => 'stopScanner=0; setNextStep();',
			'title'=>GetMessage('FILEINSPECTOR_ADMIN_SCANNER_START')
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

		<?=GetMessage('FILEINSPECTOR_ADMIN_SCANNER_BUSER_RECCOMEND');?>

		<?$tabControl->Buttons();?>

		<?foreach($stateVAriants as $cell=>$val):?>
			<input type="<?=$val["type"]?>"
				   name="<?=$val["name"]?>"
				   id="<?='b'.$val["name"]?>"
				   value="<?=$val["title"]?>"
				   title="<?=$val["title"]?>"
				   class="<?=$val["class"]?>"
			  	<?if (!$val["state"]) echo "disabled='disabled'"?>
				<?if ($val["onclick"]) echo "onclick='".$val["onclick"]."'"?>
				<?if (!$val["display"]) echo "style='display: none'";?>
				>
		<?endforeach;?>

		<?=bitrix_sessid_post();?>

		<?$tabControl->End();?>
	</form>

<?
endif;

CJSCore::Init('jquery');
?>

<script>

	var stopScanner = 0;

	function setNextStep(){

		//alert(17);

		ShowWaitWindow();
		$.ajax({
			url: 'alexkova.fileinspector_fuser_step.php?tmi='+Math.random()+'&stopscan='+stopScanner+'&NS='+'<?=$NS["ALL_CNT"]?>',
			success: function(data){

				$('#state-information').html(data);
				$('#state-information').css('display', 'block');

				//console.log(data);

				indicator = $('#state-indicator').html();
				CloseWaitWindow();

				if (indicator == 'start' || indicator == 'progress'){

					$('#bStop').css('display', 'inline');
					$('#bStop').prop('disabled', false);
					$('#bStart').prop('disabled', true);
					$('#bCont').css('display', 'none');

					if (stopScanner == 0) {
						setTimeout('setNextStep()', 200);
					}else{
						indicator = 'stop';
					}


				}

				if (indicator == 'stop' || indicator == 'success'){
					$('#bCont').css('display', 'none');
					$('#bStop').css('display', 'none');
					$('#bStart').prop('disabled', false);
				}

				if (indicator == 'success'){
					$('#bStart').prop('disabled', true);
				}

			}
		});
	}

</script>

<?

CAFISearcher::showDocNotes();


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>