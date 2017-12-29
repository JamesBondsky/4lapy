<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Callback;

use FourPaws\App\Application as App;

/**
 * Class CallBackService
 *
 * @package FourPaws\External
 */
class CallBackService
{
    const HREF = 'https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=#phone#&startparam2=#dateTime#&async=0&timeout=#timeout#';
    
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
        App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_serv_producer')->publish($href);
    }
}
