<?
#################################
#   Developer: Lynnik Danil     #
#   Site: http://bxmod.ru       #
#   E-mail: support@bxmod.ru    #
#################################

if( !$USER->CanDoOperation('edit_other_settings') )
{
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$module_id = "bxmod.auth";
CModule::IncludeModule($module_id);

$setting = BxmodAuth::GetSettings();

$aTabs = array(
    0 => array("DIV" => "edit1", "TAB" => GetMessage("BXMOD_AUTH_BASIC_SETTINGS"), "ICON" => "", "TITLE" => GetMessage("BXMOD_AUTH_BASIC_SETTINGS_TITLE")),
    1 => array("DIV" => "edit2", "TAB" => GetMessage("BXMOD_AUTH_LOGIN_SETTINGS"), "ICON" => "", "TITLE" => GetMessage("BXMOD_AUTH_LOGIN_SETTINGS_TITLE")),
    2 => array("DIV" => "edit3", "TAB" => GetMessage("BXMOD_AUTH_SOCIAL_SETTINGS"), "ICON" => "", "TITLE" => GetMessage("BXMOD_AUTH_SOCIAL_SETTINGS_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if( isset( $_POST["Apply"] ) || isset( $_POST["RestoreDefaults"] ) && check_bitrix_sessid() )
{
    if ( isset( $_POST["RestoreDefaults"] ) )
    {
        COption::RemoveOption($module_id);
    }
    else
    {
        $newOptions = Array();
        
        foreach ( $setting AS $V )
        {
            foreach ( $V AS $v )
            {
                if ( $v["FIELD"] == 'checkbox' )
                {
                    $newOptions[ $v["ID"] ] = isset( $_POST[ $v["ID"] ] ) ? "Y" : "N";
                }
                else
                {
                    $newOptions[ $v["ID"] ] = isset( $_POST[ $v["ID"] ] ) ? $_POST[ $v["ID"] ] : $v["DEFAULT"];
                }
            }
        }
        COption::SetOptionString($module_id, "options", serialize( $newOptions ));
    }
    
    LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam().($_REQUEST["siteTabControl_active_tab"] <> ''? "&siteTabControl_active_tab=".urlencode($_REQUEST["siteTabControl_active_tab"]):''));
}
?>

<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=urlencode(LANGUAGE_ID)?>">
<?
$tabControl->Begin();

foreach ( $aTabs AS $key=>$tab ):
    $tabControl->BeginNextTab();
    foreach ( $setting[ $key ] AS $field ):
?>
        <?if( $field["HEADING"] ):?>
        <tr class="heading">
            <td colspan="2"><?=$field["HEADING"]?></td>
        </tr>
        <?endif?>
        <tr>
            <td width="50%" class="field-name">
                <?=$field["NAME"]?>:
            </td>
            <td width="50%" style="padding-left: 7px;">
                <?if( $field["FIELD"] == "checkbox" ):?>
                    <?$checked = $field["VALUE"] == "Y" ? ' checked="checked"' : ''?>
                    <input type="checkbox" name="<?=$field["ID"]?>" value="Y" <?=$checked?>>
                <?elseif( $field["FIELD"] == "text" ):?>
                    <input type="text" name="<?=$field["ID"]?>" value="<?=$field["VALUE"]?>">
                <?elseif( $field["FIELD"] == "textarea" ):?>
                    <textarea rows="3" cols="25" name="<?=$field["ID"]?>"><?=$field["VALUE"]?></textarea>
                <?endif?>
            </td>
        </tr>
        <?if( $field["MESSAGE"] ):?>
        <tr>
            <td align="center" colspan="2">
                <div class="adm-info-message-wrap" align="center">
                    <div class="adm-info-message"><?=$field["MESSAGE"]?></div>
                </div>
            </td>
        </tr>
        <?endif?>
<?
    endforeach;
endforeach;
$tabControl->Buttons();?>
    <input type="hidden" name="siteTabControl_active_tab" value="<?=htmlspecialcharsbx($_REQUEST["siteTabControl_active_tab"])?>">
    <input type="submit" name="Apply" class="adm-btn-save" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
    <input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
    <?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>