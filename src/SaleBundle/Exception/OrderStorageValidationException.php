<?php

namespace FourPaws\SaleBundle\Exception;

use FourPaws\SaleBundle\Entity\OrderStorageValidationResult;
use Throwable;

class OrderStorageValidationException extends ValidationException
{
    /**
     * @var string
     */
    protected $step = '';

    /**
     * @var string
     */
    protected $realStep;

    public function __construct(
        OrderStorageValidationResult $validationResult,
        $message = '',
        $code = 0,
        Throwable $previous = null
    )
    {
        $this->step = $validationResult->getStep();
        $this->realStep = $validationResult->getRealStep();

        parent::__construct($validationResult->getErrors(), 'Wrong entity passed ');
    }

    /**
     * @return string
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * @return string
     */
    public function getRealStep(): string
    {
        return $this->realStep;
    }
}
