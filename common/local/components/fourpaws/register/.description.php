<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = array(
    'NAME'        => 'Новый компонент',
    'DESCRIPTION' => 'Реализация нестандартных функциональных возможностей 1С-Битрикс',
    'SORT'        => 10010,
    'CACHE_PATH'  => 'Y',
    'PATH'        => array(
        'ID' => 'adv',
	),
);