<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Helpers\DateHelper;

/** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
$router = Application::getInstance()->getContainer()->get('router');
/** @var Symfony\Component\Routing\RouteCollection $routes */
$routes = $router->getRouteCollection();

$arResult['SHOP_LIST_URL'] = $routes->get('fourpaws_store_ajax_storelist_choosecity')->getPath();


if ($delivery = $arResult['DELIVERY']) {
    $weekDays = $delivery['WEEK_DAYS'];

    foreach ($weekDays as $i => $day) {
        $weekDays[$i] = DateHelper::formatDate('l', strtotime(
            \sprintf('next %s', jddayofweek($day - 1, CAL_DOW_SHORT)))
        );
    }

    $arResult['DELIVERY']['WEEK_DAYS'] = $weekDays;
}
