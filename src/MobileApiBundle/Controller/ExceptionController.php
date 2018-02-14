<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use FourPaws\MobileApiBundle\Util\ExceptionDataMap;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController
{
    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var ExceptionDataMap
     */
    protected $exceptionDataMap;

    public function __construct(ViewHandlerInterface $viewHandler, ExceptionDataMap $exceptionDataMap)
    {
        $this->viewHandler = $viewHandler;
        $this->exceptionDataMap = $exceptionDataMap;
    }

    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $code = $this->getStatusCode($exception);
        $templateData = $this->getTemplateData($currentContent, $code, $exception, $logger);

        $view = $this->createView($exception, $code, $templateData);
        return $this->viewHandler->handle($view);
    }

    /**
     * @param \Exception $exception
     * @param int        $code
     * @param array      $templateData
     *
     * @return View
     */
    protected function createView(\Exception $exception, $code, array $templateData): View
    {
        $view = new View(
            $exception,
            $code,
            $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []
        );
        $view->setTemplateVar('raw_exception');
        $view->setTemplateData($templateData);

        return $view;
    }

    /**
     * Determines the status code to use for the response.
     *
     * @param \Exception $exception
     *
     * @return int
     */
    protected function getStatusCode(\Exception $exception): int
    {
        // If matched
        if ($statusCode = $this->exceptionDataMap->resolveStatusCode($exception)) {
            return $statusCode;
        }

        // Otherwise, default
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * Determines the template parameters to pass to the view layer.
     *
     * @param string               $currentContent
     * @param int                  $code
     * @param \Exception           $exception
     * @param DebugLoggerInterface $logger
     *
     * @return array
     */
    private function getTemplateData($currentContent, $code, \Exception $exception, DebugLoggerInterface $logger = null)
    {
        return [
            'exception'      => FlattenException::create($exception),
            'status'         => 'error',
            'status_code'    => $code,
            'status_text'    => array_key_exists(
                $code,
                Response::$statusTexts
            ) ? Response::$statusTexts[$code] : 'error',
            'currentContent' => $currentContent,
            'logger'         => $logger,
        ];
    }

    /**
     * Gets and cleans any content that was already outputted.
     *
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @return string
     */
    private function getAndCleanOutputBuffering($startObLevel)
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }
        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }
}
