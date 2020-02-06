<?php
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SystemException;
use \Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог


$prava = $APPLICATION->GetGroupRight("bendersay.exportimport");

if (!$prava >= "R") { // проверка уровн€ доступа к модулю
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
// установим заголовок страницы
$APPLICATION->SetTitle(GetMessage("BENDERSAY_EXPORTIMPORT_TITLE"));

// языковые файлы
Loc::loadMessages(__FILE__); 

if (!\Bitrix\Main\Loader::includeModule('bendersay.exportimport')) {
	CAdminMessage::ShowMessage(GetMessage('BENDERSAY_EXPORTIMPORT_ERROR_MODULE'));
	return false;
}

// —оздаем объект
try {
	try {
		$hlsVisual = \Bendersay\Exportimport\Helper::GetAllHL();
	} catch (SystemException $exception) {
		CAdminMessage::ShowMessage($exception->getMessage());
	}
} catch (SystemException $exception) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $exception->getMessage() . ' <a href="/bitrix/admin/module_admin.php?lang=ru">”правление модул€ми</a>',
		'HTML' => true
		]);
	return false;
}

// ¬изуальный вывод
$aTabs = array(
	array(
		'DIV' => 'export',
		'TAB' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_TITLE_EXPORT'),
		'TITLE' => Loc::getMessage('BENDERSAY_EXPORTIMPORT_TITLE_EXPORT')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>

<div id="bendersay_exportimport_result_AJAX"></div>
<form name="form_tools" method="POST" action="/bitrix/admin/bendersay_exportimport_ajax.php" id="bendersay_exportimport_form">
	<input type="hidden" name="type" value="4">
	<input type="hidden" name="export_step_id" value="0" id="export_step_id">
	<?=bitrix_sessid_post()?>
	<?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_O_SET')?></td>
	</tr>
	<tr>
		<td width="40%"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_FIELD_EXPORT_FILE')?>:</td>
		<td>
			<input type="text" id="url_data_file" size="30" value="" name="url_data_file" />
			<input type="button" value="..." OnClick="BtnClick()">
			<?
			CAdminFileDialog::ShowScript
			(
				Array(
					'event' => 'BtnClick',
					'arResultDest' => array('FORM_NAME' => 'form_tools', 'FORM_ELEMENT_NAME' => 'url_data_file'),
					'arPath' => array('SITE' => SITE_ID, 'PATH' =>Option::get("bendersay.exportimport", "url_data_file")),
					'select' => 'F',// F - file only, D - folder only
					'operation' => 'S',// O - open, S - save
					'showUploadTab' => true,
					'showAddToMenuTab' => false,
					'fileFilter' => 'json',
					'allowAllFiles' => true,
					'SaveConfig' => true,
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_HL')?>:</td>
		<td>
			<select id="hl_id" name="hl_id">
				<option value="0"></option>
				<?foreach ($hlsVisual as $row):?>
				<option value="<?= intval($row['ID'])?>"><?= htmlspecialcharsbx($row['NAME_LANG'])?> [<?= $row['ID']?>]</option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="export_hl"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_HLS')?></label>:</td>
		<td>
			<input type="radio" id="export_hl" value="export_hl" name="export_type" />
		</td>
	</tr>
	<tr>
		<td><label for="export_data"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_DATA')?></label>:</td>
		<td>
			<input type="radio" id="export_data" value="export_data" name="export_type" checked="checked" />
		</td>
	</tr>
	<tr>
		<td><label for="export_count_row"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_EXPORT_COUNT_ROW')?></label>:</td>
		<td>
			<input type="number" id="export_count_row" value="10000" name="export_count_row" checked="checked" min="1" />
		</td>
	</tr>
	<tr>
		<td><label for="export_userentity"><?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_EXPORT_USERENTITY')?></label>:</td>
		<td id="export_userentity"></td>
	</tr>

	<?$tabControl->Buttons();?>
	<input type="submit" value="<?= Loc::getMessage('BENDERSAY_EXPORTIMPORT_START_EXPORT')?>" class="adm-btn-save" id="bendersay_exportimport_submit">
	<?$tabControl->End();?>
</form>
<?

// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");