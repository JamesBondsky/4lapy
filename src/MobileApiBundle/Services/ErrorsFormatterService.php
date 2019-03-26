<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\MobileApiBundle\Dto\Error;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ErrorsFormatterService
{
    public function covertList(ConstraintViolationListInterface $constraintViolationList): ArrayCollection
    {
        $errors = new ArrayCollection();
        /**
         * @var ConstraintViolationInterface $symfonyError
         */
        foreach ($constraintViolationList as $symfonyError) {
            if ($error = $this->convertViolation($symfonyError)) {
                $errors->add($error);
            }
        }
        return $errors;
    }

    /**
     * @param ConstraintViolationInterface $constraintViolation
     *
     * @return null|Error
     */
    public function convertViolation(ConstraintViolationInterface $constraintViolation)
    {
        /**
         * @todo Error code
         */
        return new Error(100, $constraintViolation->getMessage());
    }
}
