<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Адреса магазинов Четыре Лапы в Вашем регионе');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Адреса магазинов Четыре Лапы в Вашем регионе");

?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:shop',
    '',
    [
        'SEF_FOLDER'        => '/shops/',
        'SEF_MODE'          => 'Y',
        'SEF_URL_TEMPLATES' => [
            'list'   => '',
            'detail' => '#ID#/',
        ],
    ]
); ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>