<?php

namespace FourPaws\PersonalBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PetRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class PetRepository extends BaseHlRepository
{
    const HL_NAME = 'Pet';
    /**
     * @var UserService
     */
    public $curUserService;
    /** @var Pet $entity */
    protected $entity;

    /**
     * PetRepository constructor.
     *
     * @inheritdoc
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(Pet::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws NotAuthorizedException
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            try {
                $this->entity->setUserId($this->curUserService->getCurrentUserId());
            } catch (NotAuthorizedException $e) {
                return false;
            }
        }

        return parent::create();
    }

    /**
     * @return ArrayCollection
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws \Exception
     */
    public function findByCurUser(): ArrayCollection
    {
        return $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $this->curUserService->getCurrentUserId()],
            ]
        );
    }
}
