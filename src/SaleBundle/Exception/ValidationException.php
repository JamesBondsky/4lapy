<?php

namespace FourPaws\SaleBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ValidationException extends \Exception implements BaseExceptionInterface
{
    /** @var ConstraintViolationListInterface */
    protected $errors;

    public function __construct(
        ConstraintViolationListInterface $errors,
        $message = '',
        $code = 0,
        Throwable $previous = null
    ) {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }
}
