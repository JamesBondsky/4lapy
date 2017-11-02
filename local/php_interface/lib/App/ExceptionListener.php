<?php

namespace FourPaws\App;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $responseEvent
     *
     * @throws \InvalidArgumentException
     */
    public function handle404Exception(GetResponseForExceptionEvent $responseEvent)
    {
        if ($responseEvent->getException() instanceof NotFoundHttpException) {
            $responseEvent->setResponse(new Response('', 404));
            
            require_once $_SERVER['DOCUMENT_ROOT'] . '/404.php';
        }
    }
}
