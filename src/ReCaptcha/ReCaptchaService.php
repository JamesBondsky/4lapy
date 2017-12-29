<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\ReCaptcha;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\SystemException;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ReCaptchaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    /**
     * ключ
     */
    const KEY = '6Le2QT4UAAAAAKq4NGsbLo7XYMJB1f84S_9PzVZR';
    
    /**
     * секретный ключ
     */
    const SECRET_KEY = '6Le2QT4UAAAAALHxRtMAzINWrPKT82LLCo02Cf9K';
    
    const HREF       = 'https://www.google.com/recaptcha/api/siteverify?secret=#secret_key#&response=#captcha_code#&remoteip=#remoteip#';
    
    /**
     * @var ClientInterface
     */
    protected $guzzle;
    
    /** @noinspection SpellCheckingInspection */
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /** @noinspection SpellCheckingInspection */
    
    /**
     * CallbackConsumerBase constructor.
     *
     * @param \GuzzleHttp\ClientInterface $guzzle
     *
     * @throws \RuntimeException
     */
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->logger = LoggerFactory::create('callbackService');
    }
    
    /**
     * @param string $additionalClass
     *
     * @return string
     */
    public function getCaptcha(string $additionalClass = '') : string
    {
        $this->addJs();
        
        return '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . static::KEY . '"></div>';
    }
    
    public function addJs()
    {
        Asset::getInstance()->addJs('https://www.google.com/recaptcha/api.js');
    }
    
    /**
     * @param string $recaptcha
     *
     * @throws \RuntimeException
     * @throws SystemException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return bool
     */
    public function checkCaptcha(string $recaptcha = '') : bool
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $context = Application::getInstance()->getContext();
        /** отменяем првоерку если запрос был без капчи */
        if (empty($recaptcha) && !$context->getRequest()->offsetExists('g-recaptcha-response')) {
            return true;
        }
        if (empty($recaptcha)) {
            $recaptcha = (string)$context->getRequest()->get('g-recaptcha-response');
        }
        $url =
            str_replace(
                [
                    '#secret_key#',
                    '#captcha_code#',
                    '#remoteip#',
                ],
                [
                    static::SECRET_KEY,
                    $recaptcha,
                    $context->getServer()->get('REMOTE_ADDR'),
                ],
                static::HREF
            );
        if (!empty($recaptcha)) {
            $res = $this->guzzle->request('get', $url);
            $data = json_decode($res->getBody()->getContents());
            if ($data->success) {
                return true;
            }
        }
        
        return false;
    }
}
