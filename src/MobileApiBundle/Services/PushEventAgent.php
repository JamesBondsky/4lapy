<?php

namespace FourPaws\MobileApiBundle\Services;


use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;

class PushEventAgent
{
    use LazyLoggerAwareTrait;

    /** @var PushEventAgent $instance */
    protected static $instance;

    private function __construct() {}
    private function __clone() {}

    /**
     * @return PushEventAgent
     */
    public static function getInstance()
    {
        if(is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Добавляет push-сообщения в очередь на отправку (добавление в табличку push_event)
     */
    public static function addPushMessagesToQueue()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('FourPaws\MobileApiBundle\Services\PushEventService');
            /** @var $pushEventService PushEventService */
            $pushEventService->handleRowsWithFile();
            $pushEventService->handleRowsWithoutFile();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }

        return '\\' . __METHOD__ . '();';
    }

    /**
     * Отправляет push-сообщения на android
     */
    public static function execPushEventsForAndroid()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('FourPaws\MobileApiBundle\Services\PushEventService');
            /** @var $pushEventService PushEventService */
            $pushEventService->execPushEventsForAndroid();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }
        return '\\' . __METHOD__ . '();';
    }

    /**
     * Отправляет push-сообщения на ios
     */
    public static function execPushEventsForIos()
    {
        try {
            $pushEventService = Application::getInstance()->getContainer()->get('FourPaws\MobileApiBundle\Services\PushEventService');
            /** @var $pushEventService PushEventService */
            $pushEventService->execPushEventsForIos();
        }
        catch (\Exception $exception)
        {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }
        // делаем задержку между отправками
        sleep(10);
        return '\\' . __METHOD__ . '();';
    }
}
