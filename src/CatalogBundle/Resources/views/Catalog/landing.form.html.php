<?php

use FourPaws\Enum\Form;
use FourPaws\Helpers\FormHelper;
use Symfony\Component\Templating\PhpEngine;

global $APPLICATION;

/**
 * @var string    $formTemplate
 * @var PhpEngine $view
 */

$APPLICATION->IncludeComponent(
    'bitrix:form.result.new',
    $formTemplate,
    [
        'CACHE_TIME'             => '3600000',
        'CACHE_TYPE'             => 'A',
        'CHAIN_ITEM_LINK'        => '',
        'CHAIN_ITEM_TEXT'        => '',
        'EDIT_URL'               => '',
        'IGNORE_CUSTOM_TEMPLATE' => 'Y',
        'LIST_URL'               => '',
        'SEF_MODE'               => 'N',
        'SUCCESS_URL'            => '',
        'USE_EXTENDED_ERRORS'    => 'Y',
        'VARIABLE_ALIASES'       => [
            'RESULT_ID'   => 'RESULT_ID',
            'WEB_FORM_ID' => 'WEB_FORM_ID',
        ],
        'WEB_FORM_ID'            => FormHelper::getIdByCode(Form::FAQ),
        'USE_CAPTCHA'            => 'N'
    ]
);
