<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchMoreOneException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
use FourPaws\External\Manzana\Model\Client;
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
    
    /**
     * PetService constructor.
     *
     * @param PetRepository $petRepository
     */
    public function __construct(PetRepository $petRepository)
    {
        $this->petRepository = $petRepository;
    }
    
    /**
     * @param array $data
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function add(array $data) : bool
    {
        $res = $this->petRepository->setEntityFromData($data, Pet::class)->create();
        if ($res) {
            $this->updateManzanaPets();
        }
        
        return $res;
    }
    
    /**
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ConstraintDefinitionException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    protected function updateManzanaPets()
    {
        $container = App::getInstance()->getContainer();
        $types     = [];
        $pets      = [];
        try {
            $pets = $this->getCurUserPets();
        } catch (NotAuthorizedException $e) {
        }
        if (\is_array($pets) && !empty($pets)) {
            /** @var Pet $pet */
            foreach ($pets as $pet) {
                $types[] = $pet->getXmlIdType();
            }
        }
        $manzanaService = $container->get('manzana.service');
        
        $client = null;
        try {
            $contactId         = $manzanaService->getContactIdByCurUser();
            $client            = new Client();
            $client->contactId = $contactId;
        } catch (ManzanaServiceContactSearchMoreOneException $e) {
        } catch (ManzanaServiceContactSearchNullException $e) {
            $client = new Client();
            $container->get(CurrentUserProviderInterface::class)->setClientPersonalDataByCurUser($client);
        } catch (ManzanaServiceException $e) {
        } catch (NotAuthorizedException $e) {
        }
        if ($client instanceof Client) {
            $this->setClientPets($client, $types);
            try {
                $manzanaService->updateContact($client);
            } catch (ManzanaServiceException $e) {
            } catch (ContactUpdateException $e) {
            }
        }
    }
    
    /**
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
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
            'bird',
            'cat',
            'dog',
            'fish',
            'rodent',
        ];
        $client->ffBird   = \in_array('bird', $types, true) ? 1 : 0;
        $client->ffCat    = \in_array('cat', $types, true) ? 1 : 0;
        $client->ffDog    = \in_array('dog', $types, true) ? 1 : 0;
        $client->ffFish   = \in_array('fish', $types, true) ? 1 : 0;
        $client->ffRodent = \in_array('rodent', $types, true) ? 1 : 0;
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
        $res = $this->petRepository->setEntityFromData($data, Pet::class)->update();
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
