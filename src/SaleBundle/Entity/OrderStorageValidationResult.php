<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Entity;


use FourPaws\SaleBundle\Enum\OrderStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class OrderStorageValidationResult
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $errors;

    /**
     * @var string
     */
    protected $step = '';

    /**
     * @var string
     */
    protected $realStep;

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     * @return OrderStorageValidationResult
     */
    public function setErrors(ConstraintViolationListInterface $errors): OrderStorageValidationResult
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string
     */
    public function getStep(): string
    {
        return $this->step;
    }

    /**
     * @param string $step
     * @return OrderStorageValidationResult
     */
    public function setStep(string $step): OrderStorageValidationResult
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return string
     */
    public function getRealStep(): string
    {
        if (null === $this->realStep) {
            $stepIndex = array_search($this->step, array_reverse(OrderStorage::STEP_ORDER), true);
            $this->realStep = $this->step;
            if ($stepIndex !== false) {
                $groups = [];
                foreach ($this->getErrors() as $error) {
                    $groups += \array_flip($error->getConstraint()->groups);
                }
                $groups = \array_keys($groups);
                foreach (OrderStorage::STEP_ORDER as $step) {
                    if (\in_array($step, $groups, true)) {
                        $this->realStep = $step;
                        break;
                    }
                }
            }
        }

        return $this->realStep;
    }

    /**
     * @param string $realStep
     * @return OrderStorageValidationResult
     */
    public function setRealStep(string $realStep): OrderStorageValidationResult
    {
        $this->realStep = $realStep;
        return $this;
    }
}