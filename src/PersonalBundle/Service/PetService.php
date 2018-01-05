<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
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
     * @param array $data
     *
     * @return bool
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws \Exception
     * @throws BitrixRuntimeException
     */
    public function add(array $data) : bool
    {
        $res = $this->petRepository->setEntityFromData($data, Pet::class)->create();
        if($res) {
            $this->updateManzanaPets();
        }
        return $res;
    }
    
    /**
     * @param array $data
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
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
        if($res) {
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
     * @throws NotAuthorizedException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
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
        if($res) {
            $this->updateManzanaPets();
        }
        return $res;
    }
    
    /**
     * @throws ServiceNotFoundException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ContactUpdateException
     * @throws ConstraintDefinitionException
     * @throws NotAuthorizedException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    protected function updateManzanaPets()
    {
        $container = App::getInstance()->getContainer();
        $types = [];
        $pets = $this->getCurUserPets();
        if(\is_array($pets) && !empty($pets)) {
            /** @var Pet $pet */
            foreach ($pets as $pet){
                $types[]=$pet->getXmlIdType();
            }
        }
        $manzanaService = $container->get('manzana.service');
        
        $contactId = $manzanaService->getContactIdByCurUser();
        if ($contactId >= 0) {
            $client = new Client();
            if ($contactId > 0) {
                $client->contactId = $contactId;
            } else {
                $container->get(CurrentUserProviderInterface::class)->setClientPersonalDataByCurUser($client);
            }
            $manzanaService->setClientPets($client, $types);
            $manzanaService->updateContact($client);
        }
    }
    
    /**
     * @param Client $client
     * @param array  $types
     */
    public function setClientPets(&$client, array $types)
    {
        /** @todo set actual types*/
        $baseTypes        =
            [
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
}
