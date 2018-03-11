<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation;

use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderPropertyVariantValidator extends ConstraintValidator
{
    /**
     * @var OrderPropertyService
     */
    protected $orderPropertyService;

    public function __construct(OrderPropertyService $orderPropertyService)
    {
        $this->orderPropertyService = $orderPropertyService;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @throws NotFoundException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OrderPropertyVariant) {
            return;
        }

        $availableValues = $this->orderPropertyService->getPropertyByCode($constraint->propertyCode)
                                                      ->getVariants();

        if (!isset($availableValues[$value])) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
