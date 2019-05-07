<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 07.05.2019
 * Time: 16:46
 */

namespace FourPaws\KioskBundle\Service;


class KioskService
{

    protected static $menu = [
        'https://www.dobrolap.ru/',
        'https://breeders.4lapy.ru/catalog/?owners=clubs',
        'https://breeders.4lapy.ru/advertisements/',
        'https://rabota4lapy.ru/',
    ];

    public static function isKioskMode () {
        return strpos( $_SERVER['HTTP_USER_AGENT'], 'PayLogicKiosk') != false;
    }

    public static function isHiddenInMenu ($url) {
        return array_search( $url, self::$menu);
    }

    public static function getHiddenMenuList () {
        return self::$menu;
    }

}