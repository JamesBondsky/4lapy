<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Callback;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class CallbackService
 *
 * @package FourPaws\Callback
 */
class CallbackService
{
    const HREF = 'https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=#phone#&startparam2=#dateTime#&async=0&timeout=#timeout#';
    
    /**
     * @param $phone
     * @param $curDate
     * @param $timeout
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function send($phone, $curDate, $timeout)
    {
        $href = str_replace(
            [
                '#phone#',
                '#dateTime#',
                '#timeout#',
            ],
            [
                $phone,
                $curDate,
                $timeout,
            ],
            static::HREF
        );
        /** @noinspection PhpUnhandledExceptionInspection */
        App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_set_producer')->publish($href);
    }
}
