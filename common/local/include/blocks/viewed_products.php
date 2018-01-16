<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок просмотренные товары
 * Используется на списочных страницах каталога, результатах поиска, карточки бренда
 * @updated: 15.01.2018
 */

/** @global $APPLICATION */
/** @var array $arParams */

$APPLICATION->IncludeFile(
    'blocks/components/viewed_products.php',
    [
        'WRAP_CONTAINER_BLOCK' => isset($arParams['WRAP_CONTAINER_BLOCK']) ? $arParams['WRAP_CONTAINER_BLOCK'] : 'Y',
        'WRAP_SECTION_BLOCK' => isset($arParams['WRAP_SECTION_BLOCK']) ? $arParams['WRAP_SECTION_BLOCK'] : 'Y',
        'SHOW_TOP_LINE' => isset($arParams['SHOW_TOP_LINE']) ? $arParams['SHOW_TOP_LINE'] : 'Y',
        'SHOW_BOTTOM_LINE' => isset($arParams['SHOW_BOTTOM_LINE']) ? $arParams['SHOW_BOTTOM_LINE'] : 'N',
    ],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок просмотренных товаров',
        'MODE' => 'php',
    ]
);
