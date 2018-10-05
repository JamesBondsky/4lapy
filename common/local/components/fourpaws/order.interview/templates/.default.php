<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\Enum\Form;
use FourPaws\Helpers\FormHelper;

/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

$APPLICATION->IncludeComponent(
    'bitrix:form.result.new',
    'order.feedback',
    [
        'CACHE_TIME' => '3600000',
        'CACHE_TYPE' => 'A',
        'CHAIN_ITEM_LINK' => '',
        'CHAIN_ITEM_TEXT' => '',
        'EDIT_URL' => '',
        'IGNORE_CUSTOM_TEMPLATE' => 'Y',
        'LIST_URL' => '',
        'SEF_MODE' => 'N',
        'SUCCESS_URL' => '',
        'USE_EXTENDED_ERRORS' => 'Y',
        'VARIABLE_ALIASES' => [
            'RESULT_ID' => 'RESULT_ID',
            'WEB_FORM_ID' => 'WEB_FORM_ID',
        ],
        'WEB_FORM_ID' => FormHelper::getIdByCode(Form::ORDER_INTERVIEW),
        'ADDITIONAL_DATA' => [
            'ORDER' => $arResult['ORDER'],
            'USER' => $arResult['USER'],
        ],
    ]
); ?>