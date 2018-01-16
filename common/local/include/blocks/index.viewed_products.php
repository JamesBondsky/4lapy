<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: просмотренные товары
 * @updated: 15.01.2018
 */

/** @global $APPLICATION */

$APPLICATION->IncludeFile(
    'blocks/components/viewed_products.php',
    [
        'SHOW_BOTTOM_LINE' => 'Y',
    ],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок просмотренных товаров',
        'MODE' => 'php',
    ]
);
