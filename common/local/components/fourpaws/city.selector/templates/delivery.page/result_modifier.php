<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;

/** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
$router = Application::getInstance()->getContainer()->get('router');
/** @var Symfony\Component\Routing\RouteCollection $routes */
$routes = $router->getRouteCollection();

$arResult['DELIVERY_INFO_URL'] = $routes->get('fourpaws_delivery_ajax_info_get')->getPath();
