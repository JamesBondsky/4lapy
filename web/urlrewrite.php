<?php
$arUrlRewrite = array(
	array(
		"CONDITION" => "#^/brand/([0-9a-zA-Z_-]+)/((index\\.php)?(\\?.*)?)?\$#",
		"RULE" => "ELEMENT_CODE=\$1",
		"ID" => "",
		"PATH" => "/brand/detail.php",
	),
	array(
        'CONDITION' => '#^/company/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/company/news/index.php',
	),
    array(
        'CONDITION' => '#^/services/articles/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/services/articles/index.php',
    ),
	array(
        'CONDITION' => '#^/personal/#',
        'RULE'      => '',
        'ID'        => 'fourpaws:personal',
        'PATH'      => '/personal/index.php',
	),
	array(
		"CONDITION" => "#^/brands/#",
		"RULE" => "",
		"ID" => "fourpaws:brands",
		"PATH" => "/brands/index.php",
	),
	array(
        'CONDITION' => '##',
        'RULE'      => '',
        'ID'        => '',
        'PATH'      => '/symfony_router.php',
	),
);