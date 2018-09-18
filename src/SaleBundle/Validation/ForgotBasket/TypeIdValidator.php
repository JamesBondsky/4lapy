<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Validation\ForgotBasket;

use Bitrix\Main\SystemException;
use FourPaws\SaleBundle\Service\ForgotBasketService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TypeIdValidator extends ConstraintValidator
{
    /**
     * @var ForgotBasketService
     */
    protected $forgotBasketService;

    /**
     * TaskTypeCodeValidator constructor.
     *
     * @param ForgotBasketService $forgotBasketService
     */
    public function __construct(ForgotBasketService $forgotBasketService)
    {
        $this->forgotBasketService = $forgotBasketService;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws SystemException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TypeId) {
            return;
        }

        if (!\in_array((int)$value, \array_flip($this->forgotBasketService->getTypes()), true)) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
