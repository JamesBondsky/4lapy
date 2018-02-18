<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Exception\NotFoundException;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderBonusPaymentValidator extends ConstraintValidator
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    public function __construct(
        OrderService $orderService,
        BasketService $basketService,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        $this->orderService = $orderService;
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
            if ($entity->getBonusSum()) {
                $this->context->addViolation($constraint->notAvailableMessage);
            }

            return;
        }

        $user = $this->currentUserProvider->getCurrentUser();
        if (!$user->getDiscountCardNumber() && $entity->getBonusSum()) {
            $this->context->addViolation($constraint->notAvailableMessage);

            return;
        }

        if ($entity->getBonusSum() < 0) {
            $this->context->addViolation($constraint->wrongValueMessage);

            return;
        }

        $maxValue = $this->orderService->getMaxBonusesForPayment($entity);
        if ($entity->getBonusSum() > $maxValue) {
            $this->context->addViolation($constraint->wrongValueMessage);
        }
    }
}
