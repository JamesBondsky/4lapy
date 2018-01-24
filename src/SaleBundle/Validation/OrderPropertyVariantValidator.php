<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\App\Application;
use FourPaws\SaleBundle\Service\OrderService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderPropertyVariantValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var OrderService $orderService */
        $orderService = Application::getInstance()->getContainer()->get(OrderService::class);

        $availableValues = $orderService->getPropertyVariants($constraint->propertyCode);
        if (!isset($availableValues[$value])) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
