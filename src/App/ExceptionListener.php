<?php

namespace FourPaws\App;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    /**
     * Show a bitrix 404 page on the Production
     *
     * @param GetResponseForExceptionEvent $responseEvent
     *
     * @throws \InvalidArgumentException
     *
     * @die
     */
    public function handle404Exception(GetResponseForExceptionEvent $responseEvent)
    {
        if ($responseEvent->getException() instanceof NotFoundHttpException) {
            $responseEvent->setResponse(new Response('', 404));
    
            require_once Application::getDocumentRoot() . '/404.php';
    
            die (1);
        }
    }
}
