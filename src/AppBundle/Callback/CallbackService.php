<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\AppBundle\Callback;

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
    private $parameters;
    
    /**
     * CallbackService constructor.
     *
     * @param array $parameters
     *
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }
    
    /**
     * @param string $phone
     * @param string $curDate
     * @param int    $timeout
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function send(string $phone, string $curDate = '', int $timeout = 0)
    {
        if (empty($curDate)) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $date    = new DateTime();
            $curDate = $date->format('Y-m-d H:i:s');
        }
        $uri = new Uri($this->parameters['baseUri']);
        $uri->addParams(
            [
                'name'        => '[VATS-ON] SiteCallBack',
                'async'       => 0,
                'startparam1' => $phone,
                'startparam2' => $curDate,
                'timeout'     => $timeout,
            ]
        );
        $uri->setPass($this->parameters['pass']);
        $uri->setUser($this->parameters['login']);
        $uri->setHost($uri->getUser() . ':' . $uri->getPass() . '@' . $uri->getHost());
        
        /** @noinspection PhpUnhandledExceptionInspection */
        $callBackProducer = App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_set_producer');
        /** @var Producer $callBackProducer */
        $callBackProducer->publish($uri->getUri());
    }
}
