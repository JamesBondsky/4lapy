<?php

namespace FourPaws\App;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    use LazyLoggerAwareTrait;

    public function __construct()
    {
        $this->withLogName('ExceptionListener');
    }

    /**
     * Show a bitrix 404 page on the Production and log non-HTTP exceptions
     *
     * @param GetResponseForExceptionEvent $responseEvent
     *
     * @throws \InvalidArgumentException
     *
     * @die
     */
    public function onError(GetResponseForExceptionEvent $responseEvent): void
    {
        $exception = $responseEvent->getException();
        if ($exception instanceof NotFoundHttpException) {
            $responseEvent->setResponse(new Response('', 404));

            require_once Application::getDocumentRoot() . '/404.php';

            die (1);
        }
        if (!$exception instanceof HttpException) {
            $this->log()->critical(sprintf('Unhandled exception: %s', $exception->getMessage()));
        }
    }
}
