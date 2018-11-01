<?php

namespace FourPaws\PersonalBundle\Repository;

use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\UserTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\AppBundle\Repository\BaseHlRepository;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\UserBundle\Entity\User;
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
    public const HL_NAME = 'Pet';
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
     * @return ArrayCollection|Pet[]
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws ObjectPropertyException
     */
    public function findByCurUser(): ArrayCollection
    {
        return $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $this->curUserService->getCurrentUserId()],
            ]
        );
    }

    /**
     * @param User|int $user
     * @return ArrayCollection|Pet[]
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws ObjectPropertyException
     */
    public function findByUser($user): ArrayCollection
    {
        $userId = $user instanceof User ? $user->getId() : $user;
        return $this->findBy(
            [
                'filter' => ['UF_USER_ID' => $userId],
            ]
        );
    }

    /**
     * @return array
     * @throws ObjectPropertyException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\SystemException
     */
    public function findPetsForBirthDayNotify()
    {
        $today = new \Bitrix\Main\Type\Date();
        $oPets = $this->getDataManager()->getList([
            'select' => ['UF_USER_ID', 'ID', 'UF_NAME', 'UF_BIRTHDAY', 'UF_TYPE', 'USER_EMAIL' => 'REF_USER.EMAIL', 'USER_NAME' => 'REF_USER.NAME', 'USER_SECOND_NAME' => 'REF_USER.SECOND_NAME', 'USER_LAST_NAME' => 'REF_USER.LAST_NAME', 'PET_TYPE' => 'REF_PET_TYPE.UF_CODE'],
            'filter' => [
                'LOGIC' => 'AND',
                ['UF_BIRTHDAY' => $today]
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ReferenceField('REF_USER',
                    UserTable::class, [
                        '=this.UF_USER_ID' => 'ref.ID'
                    ]),
                new \Bitrix\Main\Entity\ReferenceField('REF_PET_TYPE',
                    'ForWho', [
                        '=this.UF_TYPE' => 'ref.ID'
                    ])
            ]
        ]);

        $arResult = [];
        foreach ($oPets as $arPet) {
            $arResult[] = [
                'PET_ID' => $arPet['ID'],
                'PET_NAME' => $arPet['UF_NAME'],
                'PET_TYPE' => $arPet['PET_TYPE'],
                'PET_BIRTHDAY' => $arPet['UF_BIRTHDAY'],
                'USER_EMAIL' => $arPet['USER_EMAIL'],
                'USER_NAME' => implode(" ", array_filter([$arPet['USER_LAST_NAME'], $arPet['USER_NAME'], $arPet['USER_SECOND_NAME']])),
            ];
        }
        return $arResult;
    }
}
