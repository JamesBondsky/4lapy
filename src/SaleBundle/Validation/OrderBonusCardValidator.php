<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\ManzanaService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OrderBonusCardValidator extends ConstraintValidator
{
    /**
     * @var ManzanaService
     */
    protected $manzanaService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    public function __construct(
        ManzanaService $manzanaService,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        $this->manzanaService = $manzanaService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$entity instanceof OrderStorage || !$constraint instanceof OrderBonusCard) {
            return;
        }

        if ($entity->getUserId() &&
            $this->currentUserProvider->getCurrentUser()->getDiscountCardNumber()
        ) {
            $this->context->addViolation($constraint->cardAlreadyExistsMessage);
        } else {
            try {
                if (!$this->manzanaService->validateCardByNumber($entity->getDiscountCardNumber())) {
                    $this->context->addViolation($constraint->cardNotValidMessage);
                }
            } catch (ManzanaServiceException $e) {
                $this->context->addViolation($constraint->cardNotValidMessage);
            }
        }
    }
}
