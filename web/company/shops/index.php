<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Магазины');
?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:shop',
    '',
    [
        'SEF_FOLDER'        => '/company/shops/',
        'SEF_MODE'          => 'Y',
        'SEF_URL_TEMPLATES' => [
            'list'   => '',
            'detail' => '#ID#/',
        ],
    ]
); ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>