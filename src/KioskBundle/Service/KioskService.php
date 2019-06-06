<?php
/**
 * Created by PhpStorm.
 * User: mmasterkov
 * Date: 07.05.2019
 * Time: 16:46
 */

namespace FourPaws\KioskBundle\Service;

use Bitrix\Main\Application;
use Bitrix\Main\Web\Cookie;
use FourPaws\App\Application as SymfonyApplication;
use FourPaws\LocationBundle\LocationService;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;
use FourPaws\UserBundle\Enum\UserLocationEnum;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserCitySelectInterface;
use FourPaws\UserBundle\Service\UserService;

class KioskService
{

    protected static $menu = [
        'https://www.dobrolap.ru/',
        'https://breeders.4lapy.ru/catalog/?owners=clubs',
        'https://breeders.4lapy.ru/advertisements/',
        'https://rabota4lapy.ru/',
    ];

    public static function isKioskMode()
    {
        return strpos($_SERVER['HTTP_USER_AGENT'], 'PayLogicKiosk') != false;
    }

    public static function isHiddenInMenu($url)
    {
        return array_search($url, self::$menu);
    }

    public static function getHiddenMenuList()
    {
        return self::$menu;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public function getAuthLink()
    {
        $curPage = Application::getInstance()->getContext()->getRequest()->getRequestUri();
        $url = $this->addParamsToUrl($curPage, ['showScan' => true]);
        return $url;
    }

    /**
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public function getBonusPageUrl()
    {
        return '/personal/bonus/';
    }

    public function getLastPageUrl()
    {
        $lastUrl = $_SESSION['LAST_PAGE_URL'];
        if (!$lastUrl) {
            $lastUrl = '/';
        }
        return $lastUrl;
    }

    /**
     * @param string $url
     * @return string
     * @throws \Exception
     */
    public function setLastPageUrl(string $url)
    {
        $_SESSION['LAST_PAGE_URL'] = $url;
        return $_SESSION['LAST_PAGE_URL'];
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    public function isRedirectToBonusAfterAuth()
    {
        $url = Application::getInstance()->getContext()->getRequest()->getRequestUri();
        $query = parse_url($url, PHP_URL_QUERY);
        return strpos($query, 'page=bonus') !== false;
    }

    public function addParamsToUrl($url, $params)
    {
        preg_match("/\?(.*)/i", $url, $matches);
        $query = $matches[1];
        if ($query) {
            $arQuery = explode("&", $query);
            foreach ($params as $key => $param) {
                $value = sprintf('%s=%s', $key, $param);
                if (in_array($value, $arQuery)) {
                    unset($params[$key]);
                }
            }
            if (!empty($params)) {
                $url .= sprintf("&%s", http_build_query($params));
            }
        } else {
            $url .= sprintf("?%s", http_build_query($params));
        }
        return $url;
    }

    public function removeParamFromUrl($key)
    {
        parse_str($_SERVER['QUERY_STRING'], $vars);
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        $query = http_build_query(array_diff_key($vars, array($key => "")));
        if($query){
            $url .= '?' . $query;
        }
        return $url;
    }


    /**
     * @param string $card
     * @return string
     */
    public function setCardNumber(string $card)
    {
        $_SESSION['KIOSK_CARD'] = $card;
        return $card;
    }

    /**
     * @return mixed
     */
    public function getCardNumber()
    {
        return $_SESSION['KIOSK_CARD'];
    }

    public function setStore(string $storeCode)
    {
        if ($store = $this->findStore($storeCode)){
            $_SESSION['STORE'] = $store->getXmlId();

            if (!empty($store->getLocation())) {
                /** @var LocationService $locationService */
                $locationService = SymfonyApplication::getInstance()->getContainer()->get('location.service');
                /** @var UserService $userService */
                $userService = SymfonyApplication::getInstance()->getContainer()->get(UserCitySelectInterface::class);

                $cityStore = $locationService->findLocationCityByCode($store->getLocation());
                $cityCurrent = $userService->getSelectedCity();
                if($cityStore['ID'] != $cityCurrent['ID']) {
                    $userService->setSelectedCity($cityStore['CODE']);

                    // при смене города куки меняются на фронте, поэтому делаем это вручную
                    // иначе будет отображаться старый город
                    $application = Application::getInstance();
                    $context = $application->getContext();

                    // на фронте есть чудесная проверка на тестовую среду, где добавляется точка к домену
                    $domain = $context->getServer()->getHttpHost();
                    if(strstr($domain, 'local')){
                        $domain = '.'.$domain;
                    }

                    $cookie = new Cookie(UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE, $cityStore['CODE'], time() + 60*60*24*60, false);
                    $cookie->setDomain($domain);
                    $cookie->setHttpOnly(false);

                    $context->getResponse()->addCookie($cookie);
                    $context->getResponse()->flush("");

                    $_COOKIE[UserLocationEnum::DEFAULT_LOCATION_COOKIE_CODE] = $cityStore['CODE'];
                }
            }

            return $store;
        }
        return false;
    }

    public function getStore()
    {
        if($_SESSION['STORE']){
            return $this->findStore($_SESSION['STORE']);
        }
        return false;
    }

    public static function getLogoutUrl()
    {
        return '/kiosk/logout/';
    }



    private function findStore(string $storeCode)
    {
        /** @var StoreService $storeService */
        $storeService = $addressService = SymfonyApplication::getInstance()->getContainer()->get('store.service');
        $store = $storeService->getStoreByXmlId($storeCode);
        return $store;
    }


}