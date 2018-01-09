<?php
/**
 * Created by PhpStorm.
 * Date: 25.12.2017
 * Time: 20:57
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Корзина');

$APPLICATION->IncludeComponent(
    'fourpaws:basket',
    '',
    [],
    false,
    ['HIDE_ICONS' => 'Y']
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';