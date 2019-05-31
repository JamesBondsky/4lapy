<?
//define('NEED_AUTH', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация на фестиваль");
?>
<? $APPLICATION->IncludeComponent('fourpaws:front_office.fest_reg',
                                  'fo.17.0',
                                  [],
                                  null,
                                  [
                                      'HIDE_ICONS' => 'Y',
                                  ]); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
