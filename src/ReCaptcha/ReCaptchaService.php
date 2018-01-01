<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\ReCaptcha;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Uri;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ReCaptchaService implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    protected $serviceUri;
    
    /**
     * @var ClientInterface
     */
    protected $guzzle;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * ключ
     */
    private $key;
    
    /** @noinspection SpellCheckingInspection */
    
    /**
     * секретный ключ
     */
    private $secretKey;
    
    /** @noinspection SpellCheckingInspection */
    
    /**
     * CallbackConsumerBase constructor.
     *
     * @param ClientInterface $guzzle
     *
     * @throws InvalidArgumentException
     * @throws ApplicationCreateException
     * @throws \RuntimeException
     */
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->logger = LoggerFactory::create('recaptcha');
        
        list(
            $this->key, $this->secretKey, $this->serviceUri
            ) = array_values(App::getInstance()->getContainer()->getParameter('recaptcha'));
    }
    
    /**
     * @param string $additionalClass
     *
     * @return string
     */
    public function getCaptcha(string $additionalClass = '') : string
    {
        $this->addJs();
        
        return '<div class="g-recaptcha' . $additionalClass . '" data-sitekey="' . $this->key . '"></div>';
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
     * @throws GuzzleException
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
        $uri = new Uri($this->serviceUri);
        $uri->addParams(
            [
                'secret'   => $this->secretKey,
                'response' => $recaptcha,
                'remoteip' => $context->getServer()->get('REMOTE_ADDR'),
            ]
        );
        if (!empty($recaptcha)) {
            $res = $this->guzzle->request('get', $uri->getUri());
            if ($res->getStatusCode() === 200) {
                $data = json_decode($res->getBody()->getContents());
                if ($data->success) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
