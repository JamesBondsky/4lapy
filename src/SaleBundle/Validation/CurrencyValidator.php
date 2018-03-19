<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use Bitrix\Currency\CurrencyManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CurrencyValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Currency) {
            return;
        }

        if (!CurrencyManager::isCurrencyExist($value)) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
