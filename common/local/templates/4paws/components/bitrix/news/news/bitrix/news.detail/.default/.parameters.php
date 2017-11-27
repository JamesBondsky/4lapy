<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arTemplateParameters = [
    'DISPLAY_DATE'         => [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_DATE'),
        'TYPE'    => 'CHECKBOX',
        'DEFAULT' => 'Y',
    ],
    'DISPLAY_NAME'         => [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_NAME'),
        'TYPE'    => 'CHECKBOX',
        'DEFAULT' => 'Y',
    ],
    'DISPLAY_PICTURE'      => [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_PICTURE'),
        'TYPE'    => 'CHECKBOX',
        'DEFAULT' => 'Y',
    ],
    'DISPLAY_PREVIEW_TEXT' => [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_TEXT'),
        'TYPE'    => 'CHECKBOX',
        'DEFAULT' => 'Y',
    ],
    'USE_SHARE'            => [
        'NAME'     => GetMessage('T_IBLOCK_DESC_NEWS_USE_SHARE'),
        'TYPE'     => 'CHECKBOX',
        'MULTIPLE' => 'N',
        'VALUE'    => 'Y',
        'DEFAULT'  => 'N',
        'REFRESH'  => 'Y',
    ],
];

/** @noinspection PhpUndefinedVariableInspection */
if ($arCurrentValues['USE_SHARE'] === 'Y') {
    $arTemplateParameters['SHARE_HIDE'] = [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_SHARE_HIDE'),
        'TYPE'    => 'CHECKBOX',
        'VALUE'   => 'Y',
        'DEFAULT' => 'N',
    ];
    
    $arTemplateParameters['SHARE_TEMPLATE'] = [
        'NAME'     => GetMessage('T_IBLOCK_DESC_NEWS_SHARE_TEMPLATE'),
        'DEFAULT'  => '',
        'TYPE'     => 'STRING',
        'MULTIPLE' => 'N',
        'COLS'     => 25,
        'REFRESH'  => 'Y',
    ];
    
    if (strlen(trim($arCurrentValues['SHARE_TEMPLATE'])) <= 0) {
        $shareComponentTemlate = false;
    } else {
        $shareComponentTemlate = trim($arCurrentValues['SHARE_TEMPLATE']);
    }
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/bitrix/main.share/util.php';
    
    $arHandlers = __bx_share_get_handlers($shareComponentTemlate);
    
    $arTemplateParameters['SHARE_HANDLERS'] = [
        'NAME'     => GetMessage('T_IBLOCK_DESC_NEWS_SHARE_SYSTEM'),
        'TYPE'     => 'LIST',
        'MULTIPLE' => 'Y',
        'VALUES'   => $arHandlers['HANDLERS'],
        'DEFAULT'  => $arHandlers['HANDLERS_DEFAULT'],
    ];
    
    $arTemplateParameters['SHARE_SHORTEN_URL_LOGIN'] = [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_SHARE_SHORTEN_URL_LOGIN'),
        'TYPE'    => 'STRING',
        'DEFAULT' => '',
    ];
    
    $arTemplateParameters['SHARE_SHORTEN_URL_KEY'] = [
        'NAME'    => GetMessage('T_IBLOCK_DESC_NEWS_SHARE_SHORTEN_URL_KEY'),
        'TYPE'    => 'STRING',
        'DEFAULT' => '',
    ];
}