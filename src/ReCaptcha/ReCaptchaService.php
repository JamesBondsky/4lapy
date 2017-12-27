<?php

namespace FourPaws\ReCaptcha;

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

class ReCaptchaService
{
    /** @noinspection SpellCheckingInspection */
    /**
     * ключ
     */
    const KEY = '6Le2QT4UAAAAAKq4NGsbLo7XYMJB1f84S_9PzVZR';
    
    /** @noinspection SpellCheckingInspection */
    /**
     * секретный ключ
     */
    const SECRET_KEY = '6Le2QT4UAAAAALHxRtMAzINWrPKT82LLCo02Cf9K';
    
    
    /**
     * @return string
     */
    public function getCaptcha() : string
    {
        $this->addJs();
        return '<div class="g-recaptcha" data-sitekey="' . static::KEY . '>"></div>';
    }
    
    public function addJs()
    {
        Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js');
    }
    
    /**
     * @param string $recaptcha
     *
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    public function checkCaptcha(string $recaptcha = '') : bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = Application::getInstance()->getContext();
        /** отменяем првоерку если запрос был без капчи */
        if(empty($recaptcha) && !$context->getRequest()->offsetExists('g-recaptcha-response')){
            return true;
        }
        if (empty($recaptcha)) {
            $recaptcha = (string)$context->getRequest()->get('g-recaptcha-response');
        }
        $url =
            'https://www.google.com/recaptcha/api/siteverify?secret=' . static::SECRET_KEY . '&response=' . $recaptcha
            . '&remoteip=' . $context->getServer()->get('REMOTE_ADDR');
        if (!empty($recaptcha)) {
            $curl = curl_init();
            if (!$curl) {
                return false;
            }
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            curl_setopt(
                $curl,
                CURLOPT_USERAGENT,
                'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16'
            );
            $curlData = curl_exec($curl);
            curl_close($curl);
            $curlData = json_decode($curlData, true);
            if ($curlData['success']) {
                return true;
            }
        }
        
        return false;
    }
}