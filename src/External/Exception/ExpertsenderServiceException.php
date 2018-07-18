<?php

namespace FourPaws\External\Exception;

use \Throwable;

/**
 * Class ExpertsenderServiceException
 *
 * @package FourPaws\External\Exception
 */
class ExpertsenderServiceException extends \Exception
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters;

    public function __construct(
        string $message = '',
        int $code = 0,
        Throwable $previous = null,
        string $method = '',
        array $parameters = []
    )
    {
        $this->method = $method;
        $this->parameters = $parameters;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
