<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Enum\Form;
use FourPaws\Helpers\FormHelper;
use Symfony\Component\HttpFoundation\Response;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
global $USER, $APPLICATION;

$APPLICATION->SetTitle('Выгрузка в Excel участников акции добролап');
if(!$USER->IsAdmin()){
    die('Доступ запрещён');
}

$APPLICATION->IncludeComponent('articul:dobrolap.excel', '', []);


//require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>