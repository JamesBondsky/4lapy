<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * use FourPaws\App\Application;
 *
 *  $css = Application::markup()->getCssFile();
 *
 * @todo replace it after markup
 */
$css = '/static/build/css/main.css';

$arTemplate = [
    'NAME'          => 'Шаблон ny2020',
    'DESCRIPTION'   => 'Шаблон ny2020',
    'EDITOR_STYLES' => [
        $css,
    ],
];
