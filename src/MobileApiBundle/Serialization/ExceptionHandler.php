<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Serialization;

use FourPaws\MobileApiBundle\Util\ExceptionDataMap;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandler implements SubscribingHandlerInterface
{
    const DEFAULT_ERROR_MESSAGE = 'Системная ошибка';
    const DEFAULT_ERROR_CODE = 1001;

    /**
     * @var ExceptionDataMap
     */
    protected $exceptionDataMap;

    /**
     * @var bool
     */
    protected $debug = true;

    /**
     * @var string
     */
    protected $defaultErrorMessage = ExceptionHandler::DEFAULT_ERROR_MESSAGE;

    /**
     * @var int
     */
    protected $defaultErrorCode = ExceptionHandler::DEFAULT_ERROR_CODE;

    public function __construct(ExceptionDataMap $exceptionDataMap)
    {
        $this->exceptionDataMap = $exceptionDataMap;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => 'json',
                'type'      => \Exception::class,
                'method'    => 'serializeToJson',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getDefaultErrorMessage(): string
    {
        return $this->defaultErrorMessage;
    }

    /**
     * @param string $defaultErrorMessage
     *
     * @return ExceptionHandler
     */
    public function setDefaultErrorMessage(string $defaultErrorMessage): ExceptionHandler
    {
        $this->defaultErrorMessage = $defaultErrorMessage;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultErrorCode(): int
    {
        return $this->defaultErrorCode;
    }

    /**
     * @param int $defaultErrorCode
     *
     * @return ExceptionHandler
     */
    public function setDefaultErrorCode(int $defaultErrorCode): ExceptionHandler
    {
        $this->defaultErrorCode = $defaultErrorCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     *
     * @return ExceptionHandler
     */
    public function setDebug(bool $debug): ExceptionHandler
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param \Exception               $exception
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        \Exception $exception,
        array $type,
        Context $context
    ): array {
        return $visitor->getNavigator()->accept(
            $this->convertToArray($exception, $context),
            [
                'name'   => 'array',
                'params' => $type['params'],
            ],
            $context
        );
    }

    /**
     * @param \Exception $exception
     * @param Context    $context
     *
     * @return array
     */
    protected function convertToArray(\Exception $exception, Context $context): array
    {
        $error = [
            'title' => $this->getMessage($exception, $this->getStatusCode($context)),
            'code'  => $this->exceptionDataMap->resolveCode($exception) ?: $this->getDefaultErrorCode(),
        ];
        if ($this->isDebug()) {
            $error['exception'] = [
                'type'  => get_class($exception),
                'title' => $exception->getMessage(),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'code'  => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return [
            'data'  => null,
            'error' => [
                $error,
            ],
        ];
    }

    /**
     * @param Context $context
     *
     * @return null|int
     */
    protected function getStatusCode(Context $context)
    {
        $statusCode = null;
        $templateData = $context->attributes->get('template_data');
        if ($templateData->isDefined()) {
            $templateData = $templateData->get();
            if (array_key_exists('status_code', $templateData)) {
                $statusCode = $templateData['status_code'];
            }
        }
        return $statusCode;
    }

    protected function getMessage(\Exception $exception, $statusCode = null)
    {
        if ($message = $this->exceptionDataMap->resolveMessage($exception)) {
            return $message;
        }


        return array_key_exists(
            $statusCode,
            Response::$statusTexts
        ) ? Response::$statusTexts[$statusCode] : $this->getDefaultErrorMessage();
    }
}
