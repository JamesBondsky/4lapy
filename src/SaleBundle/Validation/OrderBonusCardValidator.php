<?php

namespace FourPaws\SaleBundle\Validation;

use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\CardNotFoundException;
use FourPaws\External\ManzanaService;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class OrderBonusCardValidator
 *
 * @package FourPaws\SaleBundle\Validation
 */
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

    /**
     * OrderBonusCardValidator constructor.
     *
     * @param ManzanaService $manzanaService
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        ManzanaService $manzanaService,
        CurrentUserProviderInterface $currentUserProvider
    )
    {
        $this->manzanaService = $manzanaService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param mixed $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$entity instanceof OrderStorage ||
            !$constraint instanceof OrderBonusCard ||
            !$entity->getDiscountCardNumber() // для того, чтобы можно было отменить привязку карты
        ) {
            return;
        }

        if ($entity->getUserId() &&
            $this->currentUserProvider->getCurrentUser()->getDiscountCardNumber()
        ) {
            $this->context->addViolation($constraint->cardAlreadyExistsMessage);
        } else {
            try {
                if (!$this->manzanaService->searchCardByNumber($entity->getDiscountCardNumber())) {
                    $this->context->addViolation(\sprintf($constraint->cardNotValidMessage, \tplvar('phone_main')));
                }
            } catch (ManzanaServiceException | CardNotFoundException $e) {
                $this->context->addViolation(\sprintf($constraint->cardNotValidMessage, \tplvar('phone_main')));
            }
        }
    }
}
