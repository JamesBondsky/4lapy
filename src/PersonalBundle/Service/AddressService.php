<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\PersonalBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Exception\NotFoundException;
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

    /** @var CurrentUserProviderInterface $currentUser */
    private $currentUser;

    /**
     * AddressService constructor.
     *
     * @param AddressRepository $addressRepository
     *
     * @throws ServiceNotFgit stashoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
        $this->currentUser = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
    }

    /**
     * @param int    $userId
     * @param string $locationCode
     *
     * @return ArrayCollection
     * @throws NotAuthorizedException
     * @throws \Exception
     */
    public function getAddressesByUser(int $userId = 0, string $locationCode = ''): ArrayCollection
    {
        return $this->addressRepository->findByUser($userId, $locationCode);
    }

    /**
     * @param int $id
     *
     * @return Address
     * @throws \Exception
     * @throws NotFoundException
     */
    public function getById(int $id): Address
    {
        return $this->addressRepository->findById($id);
    }

    /**
     * @param $data
     *
     * @deprecated
     *
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws NotAuthorizedException
     * @throws EmptyEntityClass
     * @throws \RuntimeException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws \Exception
     * @return bool
     */
    public function addFromArray(array $data): bool
    {
        $address = $this->addressRepository->dataToEntity($data, Address::class);

        return $this->add($address);
    }

    /**
     * @param Address $address
     *
     * @return bool
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ApplicationCreateException
     * @throws \Exception
     */
    public function add(Address $address): bool
    {
        if (!$address->getUserId()) {
            $address->setUserId($this->currentUser->getCurrentUserId());
        }

        if (!$address->getName()) {
            $address->setName($address->getFullAddress());
        }

        if ($address->isMain()) {
            $this->disableMainItem();
        }

        $address->setCityLocationByEntity();
        $res = $this->addressRepository->setEntity($address)->create();
        if ($res && $address->isMain()) {
            /** @noinspection PhpParamsInspection */
            $this->updateManzanaAddress($address);
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
                        'UF_USER_ID' => $this->currentUser->getCurrentUserId(),
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
        } catch (\Exception $e) {
        }
    }

    /**
     * @param Address $address
     *
     * @throws NotAuthorizedException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     */
    protected function updateManzanaAddress(Address $address)
    {
        $container = App::getInstance()->getContainer();
        /** @var ManzanaService $manzanaService */
        $manzanaService = $container->get('manzana.service');
        $client = null;
        try {
            $contactId = $manzanaService->getContactIdByCurUser();
            $client = new Client();
            $client->contactId = $contactId;
        } catch (ManzanaServiceException $e) {
            $client = new Client();
            $this->currentUser->setClientPersonalDataByCurUser($client);
        }

        if ($client instanceof Client) {
            $this->setClientAddress($client, $address);
            $manzanaService->updateContactAsync($client);
        }
    }

    /**
     * @param Client $client
     * @param Address $address
     */
    public function setClientAddress(&$client, Address $address)
    {
        /** неоткуда взять область для обновления
         * $client->addressStateOrProvince = '';*/
        $client->addressCity = $address->getCity();//Город
        $client->address = $address->getStreet();//Улица
        $client->addressLine2 = $address->getHouse();//Дом
        $client->addressLine3 = $address->getHousing();//Корпус
        $client->plAddressFlat = $address->getFlat();//Квартира
    }

    /**
     * @param array $data
     *
     * @throws NotAuthorizedException
     * @throws EmptyEntityClass
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ValidationException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function update(array $data): bool
    {
        if ($data['UF_MAIN'] === 'Y') {
            $this->disableMainItem();
        }

        /** @var Address $entity */
        $entity = $this->addressRepository->dataToEntity($data, Address::class);
        $entity->setCityLocationByEntity();
        $res = $this->addressRepository->setEntity($entity)->update();
        if ($res && $data['UF_MAIN'] === 'Y') {
            /** @noinspection PhpParamsInspection */
            $this->updateManzanaAddress($this->addressRepository->dataToEntity($data, Address::class));
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
    public function delete(int $id): bool
    {
        return $this->addressRepository->delete($id);
    }
}
