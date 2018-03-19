<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Main\ObjectPropertyException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use JMS\Serializer\ArrayTransformerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ReferralRepository
 *
 * @package FourPaws\PersonalBundle\Repository
 */
class ReferralRepository extends BaseHlRepository
{
    public const HL_NAME = 'Referral';

    /**
     * @var UserService
     */
    public $curUserService;

    /** @var Referral $entity */
    protected $entity;

    /**
     * ReferralRepository constructor.
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
        $this->setEntityClass(Referral::class);
        $this->curUserService = $currentUserProvider;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws NotAuthorizedException
     * @return bool
     */
    public function create(): bool
    {
        if ($this->entity->getUserId() === 0) {
            $this->entity->setUserId($this->curUserService->getCurrentUserId());
        }

        return parent::create();
    }

    /**
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @return ArrayCollection|Referral[]
     * @throws ObjectPropertyException
     */
    public function findByCurUser(): ArrayCollection
    {
        $referrals = $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $this->curUserService->getCurrentUserId()],
                'ttl'    => 360000,
            ]
        );

        return $referrals;
    }
}
