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
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Repository\AddressRepository;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Exception\ValidationException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class AddressService
 *
 * @package FourPaws\PersonalBundle\Service
 */
class AddressService
{
    /**
     * @var AddressRepository
     */
    private $addressRepository;
    
    /**
     * AddressService constructor.
     *
     * @param AddressRepository $addressRepository
     */
    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }
    
    /**
     * @throws ServiceNotFoundException
     * @throws \Exception
     * @throws ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @return array
     */
    public function getCurUserAddresses() : array
    {
        return $this->addressRepository->findByCurUser();
    }
    
    /**
     * @param $data
     *
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws \Exception
     * @return bool
     */
    public function add(array $data) : bool
    {
        if ($data['UF_MAIN'] === 'Y') {
            $this->disableMainItem();
        }
        
        $res = $this->addressRepository->setEntityFromData($data, Address::class)->create();
        if ($res) {
            if ($data['UF_MAIN'] === 'Y') {
                /** @noinspection PhpParamsInspection */
                $this->updateManzanaAddress($this->addressRepository->dataToEntity($data, Address::class));
            }
        }
        
        return $res;
    }
    
    /**
     *
     */
    public function disableMainItem()
    {
        try {
            $addresses = $this->addressRepository->findBy(
                [
                    'filter'      => [
                        'UF_USER_ID' => App::getInstance()
                                           ->getContainer()
                                           ->get(CurrentUserProviderInterface::class)
                                           ->getCurrentUserId(),
                        'UF_MAIN'    => 'Y',
                    ],
                    'entityClass' => Address::class,
                ]
            );
            /** @var Address $address */
            foreach ($addresses as $address) {
                $address->setMain(false);
                $this->addressRepository->setEntity($address)->update();
            }
        } catch (ApplicationCreateException $e) {
        } catch (NotAuthorizedException $e) {
        } catch (ServiceCircularReferenceException $e) {
        } catch (\Exception $e) {
        }
    }
    
    /**
     * @param Address $address
     *
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ManzanaServiceException
     * @throws ContactUpdateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     */
    protected function updateManzanaAddress(Address $address)
    {
        $manzanaService = App::getInstance()->getContainer()->get('manzana.service');
        
        $contactId = $manzanaService->getContactIdByCurUser();
        if ($contactId >= 0) {
            $client = new Client();
            if ($contactId > 0) {
                $client->contactId = $contactId;
            } else {
                $manzanaService->setClientPersonalDataByCurUser($client);
            }
            $manzanaService->setClientAddress($client, $address);
            $manzanaService->updateContact($client);
        }
    }
    
    /**
     * @param array $data
     *
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ContactUpdateException
     * @throws ManzanaServiceException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function update(array $data) : bool
    {
        if ($data['UF_MAIN'] === 'Y') {
            $this->disableMainItem();
        }
        
        $res = $this->addressRepository->setEntityFromData($data, Address::class)->update();
        if ($res) {
            if ($data['UF_MAIN'] === 'Y') {
                /** @noinspection PhpParamsInspection */
                $this->updateManzanaAddress($this->addressRepository->dataToEntity($data, Address::class));
            }
        }
        
        return $res;
    }
    
    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function delete(int $id) : bool
    {
        return $this->addressRepository->delete($id);
    }
}
