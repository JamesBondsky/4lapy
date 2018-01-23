<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * Блок на главной странице: популярные товары
 * @updated: 18.01.2018
 */

/** @global $APPLICATION */

$APPLICATION->IncludeFile(
    'blocks/components/popular_products.php',
    [],
    [
        'SHOW_BORDER' => false,
        'NAME' => 'Блок популярных товаров',
        'MODE' => 'php',
    ]
);
