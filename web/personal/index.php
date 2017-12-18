<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Профиль');
?>
<?php $APPLICATION->IncludeComponent(
    'fourpaws:personal',
    '',
    [
        'MESSAGE_404'       => '',
        'SEF_FOLDER'        => '/personal/',
        'SEF_MODE'          => 'Y',
        'SEF_URL_TEMPLATES' => [
            'address'   => 'address/',
            'bonus'     => 'bonus/',
            'orders'    => 'orders/',
            'personal'  => '',
            'pets'      => 'pets/',
            'referal'   => 'referal/',
            'subscribe' => 'subscribe/',
            'top'       => 'top/',
        ],
        'SET_STATUS_404'    => 'N',
        'SHOW_404'          => 'N',
    ]
); ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>