<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use FourPaws\SaleBundle\Service\OrderService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderPropertyVariantValidator extends ConstraintValidator
{
    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OrderPropertyVariant) {
            return;
        }

        $availableValues = $this->orderService->getPropertyByCode($constraint->propertyCode)
                                              ->getVariants();

        if (!isset($availableValues[$value])) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
