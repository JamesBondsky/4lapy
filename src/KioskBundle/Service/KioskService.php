<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 07.05.2019
 * Time: 16:46
 */

namespace FourPaws\KioskBundle\Service;

use Bitrix\Main\Application;
use Symfony\Component\HttpFoundation\Request;

class KioskService
{

    protected static $menu = [
        'https://www.dobrolap.ru/',
        'https://breeders.4lapy.ru/catalog/?owners=clubs',
        'https://breeders.4lapy.ru/advertisements/',
        'https://rabota4lapy.ru/',
    ];

    public static function isKioskMode ()
    {
        return strpos( $_SERVER['HTTP_USER_AGENT'], 'PayLogicKiosk') != false;
    }

    public static function isHiddenInMenu ($url)
    {
        return array_search( $url, self::$menu);
    }

    public static function getHiddenMenuList ()
    {
        return self::$menu;
    }

    public function getAuthLink () {
        $curPage = Application::getInstance()->getContext()->getRequest()->getRequestUri();
        $url = $this->addParamsToUrl($curPage, ['showScan' => true]);
        return $url;
    }

    public function getLastPageUrl(Request $request)
    {
        $lastUrl = $request->headers->get('referer');
        if(!$lastUrl){
            $lastUrl = '/';
        }
        return $lastUrl;
    }

    public function isRedirectToBonusAfterAuth()
    {
        $url = Application::getInstance()->getContext()->getRequest()->getRequestUri();
        $query = parse_url($url, PHP_URL_QUERY);
        return strpos($query, 'page=bonus') !== false;
    }

    public function addParamsToUrl($url, $params)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            $arQuery = explode("&", $query);
            foreach ($params as $key => $param){
                $value = sprintf('%s=%s', $key, $param);
                if(in_array($value, $arQuery)){
                    unset($params[$key]);
                }
            }
            if (!empty($params)){
                $url .= sprintf("&%s", http_build_query($params));
            }
        } else {
            $url .= sprintf("?%s", http_build_query($params));
        }
        return $url;
    }

    public function removeParamFromUrl($key) {
        parse_str($_SERVER['QUERY_STRING'], $vars);
        $url = strtok($_SERVER['REQUEST_URI'], '?') . http_build_query(array_diff_key($vars,array($key=>"")));
        return $url;
    }


}