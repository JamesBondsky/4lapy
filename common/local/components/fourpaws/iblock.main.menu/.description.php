<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = array(
    'NAME' => \Bitrix\Main\Localization\Loc::getMessage('FOURPAWS.IBLOCK_MAIN_MENU.COMPONENT_NAME'),
    'DESCRIPTION' => \Bitrix\Main\Localization\Loc::getMessage('FOURPAWS.IBLOCK_MAIN_MENU.COMPONENT_DESCRIPTION'),
    'ICON' => '/images/icon.gif',
    'CACHE_PATH' => 'Y',
    'SORT' => 500,
    'PATH' => array(
        'ID' => 'fourpaws',
        'NAME' => \Bitrix\Main\Localization\Loc::getMessage('FOURPAWS.COMPONENTS'),
        'CHILD' => array(
            'SORT' => 10,
            'ID' => 'iblock_data',
            'NAME' => \Bitrix\Main\Localization\Loc::getMessage('FOURPAWS.IBLOCK_DATA.GROUP_NAME'),
        ),
    ),
);
