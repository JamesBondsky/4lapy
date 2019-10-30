<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $USER;

if ($USER->isAdmin()) {
    echo '<pre>';
    print_r($_SERVER['REQUEST_URI']);
    echo '</pre>';
}
