<?php

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Main\ObjectPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class AddressRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class AddressRepository extends BaseHlRepository
{
    public const HL_NAME = 'Address';
    /**
     * @var UserService
     */
    public $curUserService;
    /** @var Address $entity */
    protected $entity;

    /**
     * AddressRepository constructor.
     *
     * @inheritdoc
     */
    public function __construct(
        ValidatorInterface $validator,
        ArrayTransformerInterface $arrayTransformer,
        CurrentUserProviderInterface $currentUserProvider
    ) {
        parent::__construct($validator, $arrayTransformer);
        $this->setEntityClass(Address::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @return bool
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            try {
                $this->entity->setUserId(
                    $this->curUserService->getCurrentUserId()
                );
            } catch (NotAuthorizedException $e) {
                return false;
            }
        }

        return parent::create();
    }

    /**
     * @param int    $userId
     * @param string $locationCode
     *
     * @return ArrayCollection|Address[]
     * @throws ObjectPropertyException
     * @throws NotAuthorizedException
     */
    public function findByUser(int $userId = 0, string $locationCode = ''): ArrayCollection
    {
        if (!$userId) {
            $userId = $this->curUserService->getCurrentUserId();
        }

        $filter['UF_USER_ID'] = $userId;

        if ($locationCode) {
            $filter['UF_CITY_LOCATION'] = $locationCode;
        }

        return $this->findBy(['filter' => $filter]);
    }
}
