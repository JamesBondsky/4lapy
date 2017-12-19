<?php
/**
 * @var CMain $APPLICATION
 */
/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->IncludeComponent(
    'fourpaws:catalog',
    '',
    [
        'SEF_FOLDER'        => '/catalog/',
        'SEF_URL_TEMPLATES' => [
//            'element'   => 'element/',
        ],
    ]
);


/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';