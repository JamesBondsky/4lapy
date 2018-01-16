<?php
/**
 * Created by PhpStorm.
 * Date: 25.12.2017
 * Time: 20:57
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

use FourPaws\SaleBundle\Service\BasketViewService;
use FourPaws\App\Application as PawsApplication;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Корзина');

echo PawsApplication::getInstance()->getContainer()->get(BasketViewService::class)->getBasketHtml();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';