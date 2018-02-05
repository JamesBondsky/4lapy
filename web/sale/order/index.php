<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

/**
 * @var CMain $APPLICATION
 */

$APPLICATION->IncludeComponent(
    'fourpaws:order',
    '',
    [
        'SET_TITLE' => 'Y',
        'SEF_MODE' => 'Y',
        'SEF_FOLDER' => '/sale/order/'
    ],
    false,
    ['HIDE_ICONS' => 'Y']
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
