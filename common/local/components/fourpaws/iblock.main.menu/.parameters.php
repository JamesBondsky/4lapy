<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!\Bitrix\Main\Loader::includeModule('iblock')) {
    return;
}

$sMessPrefix = 'FOURPAWS.IBLOCK_MAIN_MENU.';

$arComponentParameters = array(
    'GROUPS' => array(
        'URL_SETTINGS' => array(
            'SORT' => 1000,
            'NAME' => \Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'G_URL_SETTINGS'),
        ),
    ),
    'PARAMETERS' => array(
        'MENU_IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => '[MENU_IBLOCK_TYPE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'MENU_IBLOCK_TYPE'),
            'TYPE' => 'STRING',
            'ADDITIONAL_VALUES' => 'N'
            'DEFAULT' => '',
        ),
        'MENU_IBLOCK_CODE' => array(
            'PARENT' => 'BASE',
            'NAME' => '[MENU_IBLOCK_CODE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'MENU_IBLOCK_CODE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ),
        'PRODUCTS_IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => '[PRODUCTS_IBLOCK_TYPE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'PRODUCTS_IBLOCK_TYPE'),
            'TYPE' => 'STRING',
            'ADDITIONAL_VALUES' => 'N'
            'DEFAULT' => '',
        ),
        'PRODUCTS_IBLOCK_CODE' => array(
            'PARENT' => 'BASE',
            'NAME' => '[PRODUCTS_IBLOCK_CODE] '.\Bitrix\Main\Localization\Loc::getMessage($sMessPrefix.'PRODUCTS_IBLOCK_CODE'),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
        ),
        'CACHE_TIME' => array(
            'DEFAULT' => 43200
        ),
    ),
);
