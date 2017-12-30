<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Callback;

use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class CallbackService
 *
 * @package FourPaws\Callback
 */
class CallbackService
{
    private $baseUri;
    
    private $login;
    
    private $pass;
    
    /**
     * CallbackService constructor.
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public function __construct()
    {
        list(
            $this->baseUri, $this->login, $this->pass
            ) = array_values(App::getInstance()->getContainer()->getParameter('callback'));
    }
    
    /**
     * @param string $phone
     * @param string $curDate
     * @param int    $timeout
     *
     * @throws \Bitrix\Main\ObjectException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function send(string $phone, string $curDate = '', int $timeout = 0)
    {
        if (empty($curDate)) {
            $date    = new DateTime();
            $curDate = $date->format('Y-m-d H:i:s');
        }
        $uri = new Uri($this->baseUri);
        $uri->addParams(
            [
                'name'        => '[VATS-ON] SiteCallBack',
                'async'       => 0,
                'startparam1' => $phone,
                'startparam2' => $curDate,
                'timeout'     => $timeout,
            ]
        );
        $uri->setPass($this->pass);
        $uri->setUser($this->login);
        $uri->setHost($uri->getUser() . ':' . $uri->getPass() . '@' . $uri->getHost());
        
        /** @noinspection PhpUnhandledExceptionInspection */
        $callBackProducer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_set_producer');
        /** @var Producer $callBackProducer */
        $callBackProducer->publish($uri->getUri());
    }
}
