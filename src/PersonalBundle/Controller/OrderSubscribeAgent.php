<?php

namespace FourPaws\PersonalBundle\Controller;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

/**
 * Class OrderSubscribeAgent
 *
 * @package FourPaws\PersonalBundle\Controller
 */
class OrderSubscribeAgent
{
    use LazyLoggerAwareTrait;

    /** @var OrderSubscribeAgent $instance */
    protected static $instance;

    private function __construct() {}
    private function __clone() {}

    /**
     * @return OrderSubscribeAgent
     */
    public static function getInstance()
    {
        if(is_null(static::$instance)) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * @param int $limit
     * @param int $checkIntervalHours
     * @return string
     */
    public static function sendOrders(int $limit = 50, int $checkIntervalHours = 3): string
    {
        try {
            /** @var OrderSubscribeService $service */
            $service = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
            $service->sendOrders($limit, $checkIntervalHours);
        } catch (\Exception $exception) {
            $instance = static::getInstance();
            $instance->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }

        return '\\' . __METHOD__ . '('.$limit.', '.$checkIntervalHours.');';
    }
}
