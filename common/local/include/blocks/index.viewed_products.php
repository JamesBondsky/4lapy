<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: просмотренные товары
 * @updated: 12.01.2018
 */

/** @global $APPLICATION */

echo '<section class="b-common-section">';
$APPLICATION->IncludeFile(
    'blocks/components/viewed_products.php',
    [],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок просмотренных товаров',
        'MODE' => 'php',
    ]
);
echo '</section>';
