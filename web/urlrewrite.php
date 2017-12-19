<?php
$arUrlRewrite = [
    [
        'CONDITION' => '#^/company/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/company/news/index.php',
    ],
    [
        'CONDITION' => '#^/personal/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:personal',
        'PATH'      => '/personal/index.php',
    ],
    [
        'CONDITION' => '#^/catalog/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:catalog',
        'PATH'      => '/catalog/index.php',
    ],
    [
        'CONDITION' => '##',
        'RULE'      => '',
        'ID'        => '',
        'PATH'      => '/symfony_router.php',
    ],
];
