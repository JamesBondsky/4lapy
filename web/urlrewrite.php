<?php
$arUrlRewrite = array(
	array(
        'CONDITION' => '#^/company/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/company/news/index.php',
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