<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

/**
 * @var CMain $APPLICATION
 */

$APPLICATION->IncludeComponent(
    'fourpaws:order.payment.result',
    '',
    [
        'ORDER_ID'   => $_REQUEST['ORDER_ID'],
        'HASH'       => $_REQUEST['HASH'],
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
