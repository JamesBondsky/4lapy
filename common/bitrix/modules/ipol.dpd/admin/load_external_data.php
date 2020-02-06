<?php
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

Loc::loadMessages(__FILE__);

CModule::IncludeModule('ipol.dpd');

$APPLICATION->SetTitle(GetMessage('LOAD_TITLE'));
?>

<form id="ipol_dpd_import_form" action="<?= $APPLICATION->GetCurPageParam() ?>" method="POST">
	<input type="hidden" name="run" value="Y">

	<?php if (isset($_REQUEST['run'])) { ?>
		<?php
			$step = Option::get(IPOLH_DPD_MODULE, 'LOAD_EXTERNAL_DATA_STEP', 'LOAD_LOCATION_ALL');

			if ($step == 'LOAD_FINISH') {
				print CAdminMessage::ShowMessage(array(
					'MESSAGE' => GetMessage($step),
					'TYPE'    => 'OK',
					'HTML'    => true,
				));
			} else {
				print CAdminMessage::ShowMessage(array(
					"MESSAGE"        => GetMessage($step),
					"DETAILS"        => GetMessage('CONTINUE', ['ON_CLICK' => 'javascript:document.getElementById(\'ipol_dpd_import_form\').submit()']),
					"TYPE"           => "PROGRESS",
					"HTML"           => true,
				)); 

				print '<script type="text/javascript">setTimeout(function(){BX.showWait(); document.getElementById(\'ipol_dpd_import_form\').submit(); }, 3000)</script>';
			}

			\Ipolh\DPD\Agents::loadExternalData();
		?>
	<?php } else { ?>
		<?php
			print CAdminMessage::ShowMessage(array(
				"MESSAGE"        => GetMessage('WARNING'),
				"DETAILS"        => GetMessage('PROCESS_DESCR'),
				"TYPE"           =>"PROGRESS",
				"HTML"           =>true,
			)); 
		?>

		<input type="submit" value="<?= GetMessage('PROCESS_RUN') ?>" class="adm-btn-save">
	<?php } ?>
</form>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");