<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Фильтр списка акций по видам питомцев в разделе Акции
 * (шаблон кэшируется)
 * result_modifier.php
 *
 * @updated: 29.12.2017
 */

$arParams['FILTER_PROPERTY_CODE'] = !empty($arParams['FILTER_PROPERTY_CODE']) ? $arParams['FILTER_PROPERTY_CODE'] : 'TYPE';

$arParams['URL_TEMPLATE'] = isset($arParams['URL_TEMPLATE']) ? trim($arParams['URL_TEMPLATE']) : '';
$arResult['PRINT_LIST'] = [];

if (!$arResult['ITEMS']) {
    return;
}

$arResult['PRINT_LIST'][$arItem['UF_XML_ID']] = [
    'NAME' => \Bitrix\Main\Localization\Loc::getMessage('SHARES_LIST_FILTER.ALL'),
    'XML_ID' => '',
    'URL' => isset($arParams['ALL_URL']) ? $arParams['ALL_URL'] : '',
];

$arList = [];
foreach ($arResult['ITEMS'] as $arItem) {
    if ($arItem['PROPERTY_'.$arParams['FILTER_PROPERTY_CODE'].'_VALUE']) {
        $arList[] = $arItem['PROPERTY_'.$arParams['FILTER_PROPERTY_CODE'].'_VALUE'];
    }
}
if ($arList) {
    $mIBlockId = !empty($arParams['IBLOCKS']) ? reset($arParams['IBLOCKS']) : false;
    $mIBlockCode = !empty($arParams['IBLOCK_CODES']) ? reset($arParams['IBLOCK_CODES']) : false;
    $arProperty = \CIBlockProperty::GetById($arParams['FILTER_PROPERTY_CODE'], $mIBlockId, $mIBlockCode)->fetch();
    if ($arProperty && isset($arProperty['USER_TYPE_SETTINGS']['TABLE_NAME'])) {
        $arHlBlock = \Bitrix\Highloadblock\HighloadBlockTable::getList(
            [
				'filter' => [
					'=TABLE_NAME' => $arProperty['USER_TYPE_SETTINGS']['TABLE_NAME'],
				]
			]
		)->fetch();
		if ($arHlBlock) {
    		$sHlEntityClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arHlBlock)->getDataClass();
    		$dbItems = $sHlEntityClass::getList(
    		    [
    		        'filter' => [
    		            '=UF_XML_ID' => $arList
    		        ],
    		        'select' => [
    		            'UF_NAME', 'UF_XML_ID',
    		            // для релазиции сортировки asc,nulls
                        new \Bitrix\Main\Entity\ExpressionField(
                            'UF_SORT_ISNULL',
                            'ISNULL(%s)',
                            'UF_SORT'
                        ),
    		        ],
    		        'order' => [
    		            'UF_SORT_ISNULL' => 'asc',
    		            'UF_SORT' => 'asc',
    		            'UF_NAME' => 'asc',
    		        ]
    		    ]
    		);
    		while ($arItem = $dbItems->fetch()) {
                if(isset($arParams['ALL_URL']))
    		    $arResult['PRINT_LIST'][$arItem['UF_XML_ID']] = [
    		        'NAME' => htmlspecialcharsbx($arItem['UF_NAME']),
    		        'XML_ID' => $arItem['UF_XML_ID'],
    		        'URL' => str_replace(
                        // макрос #SECTION_CODE# используется для фильтрации по видам питомцев
    		            ['#SECTION_CODE#'],
    		            [htmlspecialcharsbx($arItem['UF_XML_ID'])],
    		            $arParams['URL_TEMPLATE']
    		        )
    		    ];
    		}
		}
    }
}
