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
    'NAME'          => 'Шаблон Grandin путешествие в Прагу' ,
    'DESCRIPTION'   => 'Шаблон Grandin путешествие в Прагу',
    'EDITOR_STYLES' => [
        $css,
    ],
];
