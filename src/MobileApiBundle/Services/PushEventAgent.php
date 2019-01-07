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
            $pushEventService = Application::getInstance()->getContainer()->get('push_event.service');
            /** @var $pushEventService PushEventService */
            $pushEventService->handleRowsWithFile();
            $pushEventService->handleRowsWithoutFile();
        }
        catch (\Exception $exception)
        {
            var_dump($exception);
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
}
