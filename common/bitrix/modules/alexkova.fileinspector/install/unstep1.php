<form action="<?echo $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <?echo CAdminMessage::ShowMessage(GetMessage("FILEINSPECTOR_UNINST_WARN"))?>
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<input type="hidden" name="id" value="alexkova.fileinspector">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">
        <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked>
		<?=GetMessage('MOD_FILEINSPECTOR_SAVEDATA')?>
		</p>
	<input type="submit" name="inst" value="<?echo GetMessage("MOD_FILEINSPECTOR_BACK")?>">
<form>
