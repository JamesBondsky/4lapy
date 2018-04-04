<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Security\SecurityException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Repository\PetRepository;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class PetService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class PetService
{
    /**
     * @var PetRepository
     */
    private $petRepository;

    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;

    /** @var ManzanaService $currentUser */
    private $manzanaService;

    /**
     * PetService constructor.
     *
     * @param PetRepository                $petRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param ManzanaService               $manzanaService
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        PetRepository $petRepository,
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaService $manzanaService
    ) {
        $this->petRepository = $petRepository;
        $this->currentUser = $currentUserProvider;
        $this->manzanaService = $manzanaService;
    }

    /**
     * @param array $data
     *
     * @throws EmptyEntityClass
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws \Exception
     * @return bool
     */
    public function add(array $data): bool
    {
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }
        if (!empty($data['UF_PHOTO_TMP'])) {
            $this->petRepository->addFileList(['UF_PHOTO' => $data['UF_PHOTO_TMP']]);
        } else {
            unset($data['UF_PHOTO']);
        }
        /** @var Pet $entity */
        $entity = $this->petRepository->dataToEntity($data, Pet::class);
        $this->petRepository->setEntity($entity);
        return $this->petRepository->create();
    }

    /**
     * @param int|User $user
     *
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ApplicationCreateException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     * @throws ObjectPropertyException
     */
    public function updateManzanaPets($user = null): void
    {
        $types = [];

        if ($user !== null) {
            $pets = $this->getUserPets($user);
        } else {
            $pets = $this->getCurUserPets();
        }

        if (!$pets->isEmpty()) {
            /** @var Pet $pet */
            foreach ($pets as $pet) {
                $types[] = $pet->getCodeType();
            }
        }

        try {
            $contactId = $this->manzanaService->getContactIdByUser();
            $client = new Client();
            $client->contactId = $contactId;
        } catch (ManzanaServiceException $e) {
            $client = new Client();
            $this->currentUser->setClientPersonalDataByCurUser($client);
        }

        $this->setClientPets($client, $types);
        $this->manzanaService->updateContactAsync($client);
    }

    /**
     * @throws ObjectPropertyException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @return ArrayCollection
     */
    public function getCurUserPets(): ArrayCollection
    {
        return $this->petRepository->findByCurUser();
    }

    /**
     * @param User|int $user
     *
     * @throws ObjectPropertyException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @return ArrayCollection
     */
    public function getUserPets($user): ArrayCollection
    {
        return $this->petRepository->findByUser($user);
    }

    /**
     * @param Client $client
     * @param array  $types
     */
    public function setClientPets(&$client, array $types): void
    {
        $baseTypes = [
            'ptitsy',
            'koshki',
            'sobaki',
            'ryby',
            'gryzuny',
            'ptitsy-gryzuny',
            'koshki-sobaki',
        ];
        $client->ffBird = \in_array('ptitsy', $types, true) || \in_array('ptitsy-gryzuny', $types, true) ? 1 : 0;
        $client->ffCat = \in_array('koshki', $types, true) || \in_array('koshki-sobaki', $types, true) ? 1 : 0;
        $client->ffDog = \in_array('sobaki', $types, true) || \in_array('koshki-sobaki', $types, true) ? 1 : 0;
        $client->ffFish = \in_array('ryby', $types, true) ? 1 : 0;
        $client->ffRodent = \in_array('gryzuny', $types, true) || \in_array('ptitsy-gryzuny', $types, true) ? 1 : 0;
        $others = 0;
        if (\is_array($types) && !empty($types)) {
            foreach ($types as $type) {
                if (!\in_array($type, $baseTypes, true)) {
                    $others = 1;
                    break;
                }
            }
        }
        $client->ffOthers = $others;
    }

    /**
     * @param array $data
     *
     * @throws SecurityException
     * @throws NotFoundException
     * @throws NotAuthorizedException
     * @throws EmptyEntityClass
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws ObjectPropertyException
     * @throws \Exception
     * @return bool
     */
    public function update(array $data): bool
    {
        if (empty($data['UF_PHOTO_TMP'])) {
            unset($data['UF_PHOTO']);
            $this->petRepository->addFileList(['UF_PHOTO' => 'skip']);
        } else {
            $this->petRepository->addFileList(['UF_PHOTO' => $data['UF_PHOTO_TMP']]);
        }

        /** @var Pet $entity */
        $entity = $this->petRepository->dataToEntity($data, Pet::class);

        $updateEntity = $this->getById($entity->getId());
        if ($updateEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        if ($entity->getUserId() === 0) {
            $entity->setUserId($updateEntity->getUserId());
        }

        return $this->petRepository->setEntity($entity)->update();
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws NotAuthorizedException
     * @throws SecurityException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @throws ObjectPropertyException
     * @throws \Exception
     * @return bool
     */
    public function delete(int $id): bool
    {
        $deleteEntity = $this->getById($id);
        if ($deleteEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        return $this->petRepository->delete($id);
    }

    /**
     * @param int $id
     *
     * @throws ObjectPropertyException
     * @throws \Exception
     * @throws NotFoundException
     * @return BaseEntity|Pet
     */
    public function getById(int $id): Pet
    {
        return $this->petRepository->findById($id);
    }
}
