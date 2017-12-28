<?php

namespace FourPaws\Callback;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use GuzzleHttp\ClientInterface;
use FourPaws\App\Application as App;

/**
 * Class CallBackService
 *
 * @package FourPaws\External
 */
class CallBackService
{
    const HREF = 'https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=#phone#&startparam2=#dateTime#&async=0&timeout=#timeout#';
    
    public function __construct(ClientInterface $guzzle, LoggerFactory $logger)
    {
        //https://srv_03:F6RIikaO9QvhlZ7C@4584.vats-on.ru/execsvcscriptplain?name=[VATS-ON] SiteCallBack&startparam1=84995516639&startparam2=2017-11-10 00:00:00&async=0&timeout=10
    }
    
    public function send($phone, $curDate, $timeout)
    {
        $href =
            str_replace(
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
        App::getInstance()->getContainer()->get('old_sound_rabbit_mq.callback_producer')->publish($href);
    }
}