<?php

namespace FourPaws\MobileApiBundle\Traits;

use FourPaws\App\Application;
use FourPaws\MobileApiBundle\Services\EmptyLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait MobileApiLoggerAwareTrait
{
    use LoggerAwareTrait;

    /**
     * @return LoggerInterface
     */
    protected function mobileApiLog() : LoggerInterface
    {
        $mobileApiLoggerService = Application::getInstance()->getContainer()->get('mobile_api.logger.service');

        if ($mobileApiLoggerService->getIsLogging()) {
            return $this->logger;
        }

        return new EmptyLogger();
    }
}
