<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);


if (!$USER->IsAdmin() || !Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
    return;
}

require __DIR__ . '/config.php';

$module_id = RBS_MODULE_ID;

$arStatuses = array();
$dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID), false, false, Array("ID", "NAME", "SORT"));
while ($arStatus = $dbStatus->GetNext()) {
    $arStatuses[$arStatus["ID"]] = "[" . $arStatus["ID"] . "] " . $arStatus["NAME"];
}

$status = COption::GetOptionString($module_id, "result_order_status", "N");
$RETURN_PAGE_VALUE = COption::GetOptionString($module_id, "RETURN_PAGE_VALUE", API_RETURN_PAGE);
$GATE_TRY = COption::GetOptionString($module_id, "GATE_TRY", API_GATE_TRY);

$iso = COption::GetOptionString($module_id, "iso", serialize(array()));
$iso = unserialize($iso);
$FISCALIZATION = COption::GetOptionString($module_id, "FISCALIZATION", serialize(array()));
$FISCALIZATION = unserialize($FISCALIZATION);

if ($REQUEST_METHOD == 'POST' && strlen($Update . $Apply) > 0 && check_bitrix_sessid()) {
    if($_POST['GATE_TRY'] < 10) { $_POST['GATE_TRY'] = 10; }

    $status = $_POST['RESULT_ORDER_STATUS'];
    COption::SetOptionString($module_id, "result_order_status", $status);
    COption::SetOptionString($module_id, "RETURN_PAGE_VALUE", $_POST['RETURN_PAGE_VALUE']);
    COption::SetOptionString($module_id, "GATE_SEND_COMMENT", serialize($_POST['GATE_SEND_COMMENT']));
    COption::SetOptionString($module_id, "GATE_TRY", $_POST['GATE_TRY']);

    $iso = $_POST['iso'];
    if (!is_array($iso))
        $iso = array();
    COption::SetOptionString($module_id, "iso", serialize($iso));
    COption::SetOptionString($module_id, "FISCALIZATION", serialize($_REQUEST['FISCALIZATION']));
    COption::SetOptionString($module_id, "VAT_LIST", serialize($_REQUEST['VAT_LIST']));
    COption::SetOptionString($module_id, "VAT_DELIVERY_LIST", serialize($_REQUEST['VAT_DELIVERY_LIST']));
}

$iso = array_filter($iso);
$arDefaultIso = unserialize(DEFAULT_ISO);
if (is_array($arDefaultIso))
    $iso = array_merge($arDefaultIso, $iso);


$VAT_LIST_SAVED = unserialize(COption::GetOptionString($module_id, "VAT_LIST", serialize(array())));
$VAT_LIST_DELIVERY_SAVED = unserialize(COption::GetOptionString($module_id, "VAT_DELIVERY_LIST", serialize(array())));

$arPaysystemVat = array(
    0 => Loc::getMessage('TAB1_VAT_LIST_VALUE_0'),
    1 => Loc::getMessage('TAB1_VAT_LIST_VALUE_1'),
    2 => Loc::getMessage('TAB1_VAT_LIST_VALUE_2'),
    3 => Loc::getMessage('TAB1_VAT_LIST_VALUE_3'),
);

$GATE_SEND_COMMENT = unserialize(COption::GetOptionString($module_id, 'GATE_SEND_COMMENT', serialize([0 => 'COMMENT'])));
if(!$GATE_SEND_COMMENT) {
    $GATE_SEND_COMMENT = [0 => 'COMMENT'];
}

$arVatList = array(
    0 => Loc::getMessage('TAB1_VAT_NOT_SET'),
);
$dbRes = CCatalogVat::GetList();
while ($arRes = $dbRes->Fetch()) {
    $arVatList[$arRes['ID']] = $arRes['NAME'];
}
// VIEW

$tabControl = new CAdminTabControl('tabControl', array(
    array('DIV' => 'edit1', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => 'ib_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'))
));

$tabControl->Begin();



if($_POST) {
    $status = $_POST['RESULT_ORDER_STATUS'];
    $FISCALIZATION['TAX_SYSTEM'] = $_POST['FISCALIZATION']['TAX_SYSTEM'];
    $FISCALIZATION['ENABLE'] = $_POST['FISCALIZATION']['ENABLE'];
    $iso['RUB'] = $_POST['iso']['RUB'];
    $iso['USD'] = $_POST['iso']['USD'];
    $iso['EUR'] = $_POST['iso']['EUR'];
    $iso['UAH'] = $_POST['iso']['UAH'];
    $iso['BYN'] = $_POST['iso']['BYN'];
    $RETURN_PAGE_VALUE = $_POST['RETURN_PAGE_VALUE'];
    $GATE_TRY = $_POST['GATE_TRY'];
}

?>
<form method="post"
      action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<? echo LANGUAGE_ID ?>">
    <?= bitrix_sessid_post() ?>

    <? $tabControl->BeginNextTab() ?>

    <tr>
        <td width="40%"><?= Loc::getMessage('RESULT_ORDER_STATUS'); ?>:</td>
        <td width="60%">
            <select name="RESULT_ORDER_STATUS">
                <?

                foreach ($arStatuses as $key => $name) {
                    ?>
                    <option value="<?= $key ?>"<?= $key == $status ? ' selected' : '' ?>><?= htmlspecialcharsex($name) ?></option><?
                }

                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td width="40%"></td>
        <td width="60%">
            <input type="button" id="check-https" value="<?= Loc::getMessage('CHECK_HTTPS'); ?>">
            <p id="result-check-https"></p>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage('TAB1_CURRENCY_TITLE') ?></td>
    </tr>
    <tr>
        <td width="40%"><?= Loc::getMessage('CURRENCY_CHOISE'); ?></td>
        <td width="60%">
            <table>
                <thead>
                <th><?= Loc::getMessage('CC_HEAD_CURRENCY'); ?></th>
                <th><?= Loc::getMessage('CC_HEAD_CODE'); ?></th>
                <th><?= Loc::getMessage('CC_HEAD_ISO'); ?></th>
                </thead>
                <tbody>
                <? $dbRes = CCurrency::GetList(($by = 'id'), ($order = 'asc'));
                while ($arItem = $dbRes->GetNext()):
                    ?>
                    <tr>
                        <td><?= $arItem["FULL_NAME"] ?></td>
                        <td><?= $arItem["CURRENCY"] ?></td>
                        <td><input name="iso[<?= $arItem["~CURRENCY"] ?>]" type="text"
                                   value="<? echo $iso[$arItem["~CURRENCY"]] ? $iso[$arItem["~CURRENCY"]] : $arItem["NUMCODE"] ?>">
                        </td>
                    </tr>
                <? endwhile; ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage('TAB1_FISCALIZATION_TITLE') ?></td>
    </tr>
    <tr>
        <td width="40%"><?= GetMessage('BANK_ISSUED_CHECK') ?></td>
        <td width="60%"><input type="checkbox" value="Y" name="FISCALIZATION[ENABLE]" <? if ($FISCALIZATION['ENABLE'] == 'Y') echo 'checked="checked"' ?>>
        </td>
    </tr>
    <tr>
        <td width="40%" class="adm-detail-content-cell-l"></td>
        <td width="60%" class="adm-detail-content-cell-r"><div class="adm-info-message" style="margin-top: 0;"><?= GetMessage('BANK_ISSUED_CHECK_DESCRIPTION') ?></div></td>
    </tr>
    <tr>
        <td width="40%"><?= GetMessage('TAX_SYSTEM') ?></td>
        <td width="60%">
            <select name="FISCALIZATION[TAX_SYSTEM]">
                <option value="0" <? if ($FISCALIZATION['TAX_SYSTEM'] == 0) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_GENERAL') ?></option>
                <option value="1" <? if ($FISCALIZATION['TAX_SYSTEM'] == 1) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_SIMPLIFIED_INCOME') ?></option>
                <option value="2" <? if ($FISCALIZATION['TAX_SYSTEM'] == 2) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_SIMPLIFIED_REVENUE_MINUS_CONSUMPTION') ?></option>
                <option value="3" <? if ($FISCALIZATION['TAX_SYSTEM'] == 3) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_SINGLE_TAX_ON_IMPUTED_INCOME') ?></option>
                <option value="4" <? if ($FISCALIZATION['TAX_SYSTEM'] == 4) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_UNIFIED_AGRICULTURAL_TAX') ?></option>
                <option value="5" <? if ($FISCALIZATION['TAX_SYSTEM'] == 5) echo 'selected' ?>><?= GetMessage('TAX_SYSTEM_PATENT_SYSTEM_OF_TAXATION') ?></option>
            </select>
        </td>
    </tr>
    <tr class="heading">
        <td colspan="2"><?= Loc::getMessage('ADVANCED_OPTIONS_TITLE') ?></td>
    </tr>

    <tr>
        <td width="40%"><?= GetMessage('GATE_SEND_COMMENT_LABEL') ?></td>
        <td width="60%">
            <select name="GATE_SEND_COMMENT[]" multiple size="5" style="width: 200px;">
                <option value="FIO" <?=(in_array('FIO', $GATE_SEND_COMMENT) ? 'selected' : '')?> ><?=Loc::getMessage('GATE_SEND_COMMENT_NAME_FIO')?></option>
                <option value="COMMENT" <?=(in_array('COMMENT', $GATE_SEND_COMMENT) ? 'selected' : '')?>><?=Loc::getMessage('GATE_SEND_COMMENT_NAME_COMMENT')?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="40%" class="adm-detail-content-cell-l"></td>
        <td width="60%" class="adm-detail-content-cell-r"><div class="adm-info-message" style="margin-top: 0;"><?= GetMessage('GATE_SEND_COMMENT_DESCRIPTION') ?><div></td>
    </tr>
    <tr>
        <td width="40%"><?= GetMessage('RETURN_PAGE_LABEL') ?></td>
        <td width="60%">
            <input name="RETURN_PAGE_VALUE" type="text" value="<?=$RETURN_PAGE_VALUE?>" size="34">
        </td>
    </tr>
    <tr>
        <td width="40%" class="adm-detail-content-cell-l"></td>
        <td width="60%" class="adm-detail-content-cell-r"><div class="adm-info-message" style="margin-top: 0;"><?= GetMessage('RETURN_PAGE_DESCRIPTION') ?><div></td>
    </tr>
    <tr>
        <td width="40%"><?= GetMessage('GATE_TRY_LABEL') ?></td>
        <td width="60%">
            <input name="GATE_TRY" type="number" min="10" value="<?=$GATE_TRY;?>">
        </td>
    </tr>
    <tr>
        <td width="40%" class="adm-detail-content-cell-l"></td>
        <td width="60%" class="adm-detail-content-cell-r"><div class="adm-info-message" style="margin-top: 0;"><?= GetMessage('GATE_TRY_DESCRIPTION') ?><div></td>
    </tr>


    <script type="text/javascript">
        BX.ready(function () {
            var oButtonCheck = document.getElementById('check-https');
            if (oButtonCheck) {
                oButtonCheck.onclick = function () {
                    BX.ajax.get('/<?= basename(dirname(__FILE__))?>/ajax.php',
                        '<?echo CUtil::JSEscape(bitrix_sessid_get())?>&check_https=Y',
                        function (result) {
                            var oResultCH = document.getElementById('result-check-https');

                            oResultCH.innerHTML = result;

                        });
                    return false;
                }
            }
        });
    </script>
    <? $tabControl->Buttons() ?>

    <input type="submit" name="Update" value="<?= GetMessage("MAIN_SAVE") ?>"
           title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save">
    <input type="submit" name="Apply" value="<?= GetMessage("MAIN_OPT_APPLY") ?>"
           title="<?= GetMessage("MAIN_OPT_APPLY_TITLE") ?>">
    <? if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
        <input type="button" name="Cancel" value="<?= GetMessage("MAIN_OPT_CANCEL") ?>"
               title="<?= GetMessage("MAIN_OPT_CANCEL_TITLE") ?>"
               onclick="window.location='<? echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'">
        <input type="hidden" name="back_url_settings" value="<?= htmlspecialcharsbx($_REQUEST["back_url_settings"]) ?>">
    <? endif ?>

    <? $tabControl->End() ?>
</form>