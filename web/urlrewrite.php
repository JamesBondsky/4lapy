<?php
$arUrlRewrite = [
    [
        'CONDITION' => '#^/customer/shares/#',
        'RULE' => '',
        'ID' => 'bitrix:news',
        'PATH' => '/customer/shares/index.php',
	],
    [
        'CONDITION' => '#^/services/articles/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/services/articles/index.php',
    ],
    [
        'CONDITION' => '#^/services/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/services/news/index.php',
	],
	[
        'CONDITION' => '#^/personal/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:personal',
        'PATH'      => '/personal/index.php',
    ],
    [
        'CONDITION' => '#^/sale/order/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:order',
        'PATH'      => '/sale/order/index.php',
    ],
    [
        'CONDITION' => '#^/brands/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:brands',
        'PATH'      => '/brands/index.php',
	],
	[
        'CONDITION' => '##',
        'RULE'      => '',
        'ID'        => '',
        'PATH'      => '/symfony_router.php',
    ],
];
