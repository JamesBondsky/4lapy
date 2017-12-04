<?php
$arUrlRewrite = array(
	array(
        'CONDITION' => '#^/company/news/#',
        'RULE'      => '',
        'ID'        => 'bitrix:news',
        'PATH'      => '/company/news/index.php',
	),
	array(
        'CONDITION' => '##',
        'RULE'      => '',
        'ID'        => '',
        'PATH'      => '/symfony_router.php',
	),
);