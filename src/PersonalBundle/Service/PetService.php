<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\External\Exception\ManzanaServiceContactSearchNullException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Exception\ContactUpdateException;
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
    
    /**
     * PetService constructor.
     *
     * @param PetRepository $petRepository
     *
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(PetRepository $petRepository)
    {
        $this->petRepository = $petRepository;
        $this->currentUser   = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
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
        $res = $this->petRepository->setEntityFromData($data, Pet::class)->addFileKey('UF_PHOTO')->create();
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
            /** @var ManzanaService $manzanaService */
            $manzanaService = $container->get('manzana.service');
    
            $client = null;
            try {
                $contactId         = $manzanaService->getContactIdByCurUser();
                $client            = new Client();
                $client->contactId = $contactId;
            } catch (ManzanaServiceContactSearchNullException $e) {
                $client = new Client();
                try {
                    $this->currentUser->setClientPersonalDataByCurUser($client);
                } catch (NotAuthorizedException $e) {
                }
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
        $res = $this->petRepository->setEntityFromData($data, Pet::class)->addFileKey('UF_PHOTO')->update();
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
