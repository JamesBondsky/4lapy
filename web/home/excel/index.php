<?

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $USER;

if(!$USER->IsAdmin()){
    die('Доступ запрещён');
}

$APPLICATION->IncludeComponent('articul:landing.home.excel', '', []);

?>