<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderBonusPaymentValidator extends ConstraintValidator
{
    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    public function __construct(
        OrderStorageService $orderStorageService,
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        $this->orderStorageService = $orderStorageService;
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderBonusPayment) {
            return;
        }

        if (!$entity->getUserId()) {
            if ($entity->getBonus()) {
                $this->context->addViolation($constraint->notAvailableMessage);
            }

            return;
        }

        $user = $this->currentUserProvider->getCurrentUser();
        if (!$user->getDiscountCardNumber() && $entity->getBonus()) {
            $this->context->addViolation($constraint->notAvailableMessage);

            return;
        }

        if ($entity->getBonus() < 0) {
            $this->context->addViolation($constraint->wrongValueMessage);

            return;
        }

        $maxValue = $this->basketService->getMaxBonusesForPayment();
        if ($entity->getBonus() > $maxValue) {
            $this->context->addViolation($constraint->wrongValueMessage);
        }
    }
}
