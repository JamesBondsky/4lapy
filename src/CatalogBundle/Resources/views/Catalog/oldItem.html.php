<?php use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Iblock\Component\Tools;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$logger = LoggerFactory::create('productDetail');

Tools::process404([], true, true, true);
$logger->error('Нет итема');
return;