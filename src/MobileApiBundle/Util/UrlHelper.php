<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Util;

use Bitrix\Main\Application;

class UrlHelper
{
    /**
     * Преобразует относительные url вида /bla/bla/ в полные http://domain.com/bla/bla/
     * @param $url
     * @return string
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFullUrl($url) {
        $host = Application::getInstance()->getContext()->getRequest()->getHttpHost();
        if (strpos($host, $url) === false) {
            $url = 'http://' . $host . $url;
        }
        return $url;
    }
}
