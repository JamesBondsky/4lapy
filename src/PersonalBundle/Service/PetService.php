<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Entity\Pet;
use FourPaws\PersonalBundle\Repository\PetRepository;
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
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(
        PetRepository $petRepository,
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaService $manzanaService
    )
    {
        $this->petRepository  = $petRepository;
        $this->currentUser    = $currentUserProvider;
        $this->manzanaService = $manzanaService;
    }
    
    /**
     * @param array $data
     *
     * @return bool
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function add(array $data) : bool
    {
        if (empty($data['UF_USER_ID'])) {
            $data['UF_USER_ID'] = $this->currentUser->getCurrentUserId();
        }
        $this->petRepository->setEntityFromData($data, Pet::class);
        if (!empty($data['UF_PHOTO_TMP'])) {
            $this->petRepository->addFileList(['UF_PHOTO' => $data['UF_PHOTO_TMP']]);
        }
        else{
            unset($data['UF_PHOTO']);
        }
        $res = $this->petRepository->create();
        if ($res) {
            $this->updateManzanaPets();
        }
        
        return $res;
    }
    
    /**
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    protected function updateManzanaPets()
    {
        $container = App::getInstance()->getContainer();
        $types     = [];

        try {
            $pets = $this->getCurUserPets();
            if (\is_array($pets) && !empty($pets)) {
                /** @var Pet $pet */
                foreach ($pets as $pet) {
                    $types[] = $pet->getCodeType();
                }
            }
            
            $client = null;
            try {
                /** @var ManzanaService $manzanaService */
                $manzanaService = $container->get('manzana.service');
    
                $contactId         = $manzanaService->getContactIdByCurUser();
                $client            = new Client();
                $client->contactId = $contactId;
            } catch (ManzanaServiceException $e) {
                $client = new Client();
                $this->currentUser->setClientPersonalDataByCurUser($client);
            }
    
            if ($client instanceof Client) {
                $this->setClientPets($client, $types);
                $manzanaService->updateContactAsync($client);
            }
        } catch (NotAuthorizedException $e) {
        }
    }
    
    /**
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @return array
     */
    public function getCurUserPets() : array
    {
        return $this->petRepository->findByCurUser();
    }
    
    /**
     * @param Client $client
     * @param array  $types
     */
    public function setClientPets(&$client, array $types)
    {
        /** @todo set actual types */
        $baseTypes        = [
            'ptitsy',
            'koshki',
            'sobaki',
            'ryby',
            'gryzuny',
            'ptitsy-gryzuny',
            'koshki-sobaki',
        ];
        $client->ffBird   = \in_array('ptitsy', $types, true) || \in_array('ptitsy-gryzuny', $types, true) ? 1 : 0;
        $client->ffCat    = \in_array('koshki', $types, true) || \in_array('koshki-sobaki', $types, true) ? 1 : 0;
        $client->ffDog    = \in_array('sobaki', $types, true) || \in_array('koshki-sobaki', $types, true) ? 1 : 0;
        $client->ffFish   = \in_array('ryby', $types, true) ? 1 : 0;
        $client->ffRodent = \in_array('gryzuny', $types, true) || \in_array('ptitsy-gryzuny', $types, true) ? 1 : 0;
        $others           = 0;
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
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function update(array $data) : bool
    {
        $this->petRepository->setEntityFromData($data, Pet::class);
        if (!empty($data['UF_PHOTO_TMP'])) {
            $this->petRepository->addFileList(['UF_PHOTO' => $data['UF_PHOTO_TMP']]);
        }
        else{
            unset($data['UF_PHOTO']);
        }
        $res = $this->petRepository->update();
        if ($res) {
            $this->updateManzanaPets();
        }
        
        return $res;
    }
    
    /**
     * @param int $id
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function delete(int $id) : bool
    {
        $res = $this->petRepository->delete($id);
        if ($res) {
            $this->updateManzanaPets();
        }
        
        return $res;
    }
}
