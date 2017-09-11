<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\App;

use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ErrorHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [['onException']],
        ];
    }

    public function onException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        //TODO Задуматься о логировании здесь исключений и о том, почему ловится именно RuntimeException, а не более общий тип Exception или Throwable

        if ($exception instanceof MethodNotAllowedHttpException) {
            $event->setResponse(new Response('', Response::HTTP_METHOD_NOT_ALLOWED));
        } elseif ($exception instanceof NotFoundHttpException) {
            $event->setResponse(new Response('', Response::HTTP_NOT_FOUND));
        } elseif ($exception instanceof RuntimeException) {
            $event->setResponse(new Response('', Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
