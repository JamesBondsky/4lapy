<?php
define('NEED_AUTH', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetPageProperty('title', 'Фестиваль - онлайн-сервис регистрации участников');
$APPLICATION->SetPageProperty('description', '');
$APPLICATION->SetTitle("Фестиваль - онлайн-сервис регистрации участников");
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';