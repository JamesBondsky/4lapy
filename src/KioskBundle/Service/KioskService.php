<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 07.05.2019
 * Time: 16:46
 */

namespace FourPaws\KioskBundle\Service;

use Symfony\Component\HttpFoundation\Request;

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

    public function getLastPageUrl(Request $request)
    {
        return $request->headers->get('referer');
    }

    public function addParamsToUrl($url, $params)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $url .= sprintf("&%s", http_build_query($params));
        } else {
            $url .= sprintf("?%s", http_build_query($params));
        }
        return $url;
    }


}