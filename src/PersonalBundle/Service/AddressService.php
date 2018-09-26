<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Security\SecurityException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\AppBundle\Exception\NotFoundException;
use FourPaws\External\Exception\ManzanaServiceException;
use FourPaws\External\Manzana\Model\Client;
use FourPaws\External\ManzanaService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\Entity\Address as LocationAddress;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Repository\OldAddressRepository;
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
     * @var OldAddressRepository
     */
    private $addressRepository;

    /**
     * @var CurrentUserProviderInterface $currentUser
     */
    private $currentUser;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * AddressService constructor.
     *
     * @param OldAddressRepository         $addressRepository
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param LocationService              $locationService
     */
    public function __construct(
        OldAddressRepository $addressRepository,
        CurrentUserProviderInterface $currentUserProvider,
        LocationService $locationService
    )
    {
        $this->addressRepository = $addressRepository;
        $this->currentUser = $currentUserProvider;
        $this->locationService = $locationService;
    }

    /**
     * @param LocationAddress $locationAddress
     *
     * @return Address
     */
    public function createFromLocation(LocationAddress $locationAddress): Address
    {
        return (new Address())->setCity($locationAddress->getCity())
                              ->setLocation($locationAddress->getLocation())
                              ->setStreet($locationAddress->getStreet())
                              ->setHouse($locationAddress->getHouse())
                              ->setHousing($locationAddress->getHousing())
                              ->setEntrance($locationAddress->getEntrance())
                              ->setFloor($locationAddress->getFloor())
                              ->setFlat($locationAddress->getFlat());
    }

    /**
     * @param int    $userId
     * @param string $locationCode
     *
     * @throws ObjectPropertyException
     * @throws NotAuthorizedException
     * @return Address[]|ArrayCollection
     */
    public function getAddressesByUser(int $userId = 0, string $locationCode = ''): ArrayCollection
    {
        return $this->addressRepository->findByUser($userId, $locationCode);
    }

    /**
     * @param int $id
     *
     * @throws ObjectPropertyException
     * @throws NotFoundException
     * @return Address|BaseEntity
     */
    public function getById(int $id): Address
    {
        return $this->addressRepository->findById($id);
    }

    /**
     * @deprecated
     *
     * @param array $data
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws CityNotFoundException
     * @throws ConstraintDefinitionException
     * @throws EmptyEntityClass
     * @throws InvalidIdentifierException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws \RuntimeException
     */
    public function addFromArray(array $data): bool
    {
        /** @var Address $entity */
        $entity = $this->addressRepository->dataToEntity($data, Address::class);

        return $this->add($entity);
    }

    /**
     * @param Address $address
     * @return bool
     * @throws ApplicationCreateException
     * @throws BitrixRuntimeException
     * @throws CityNotFoundException
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws NotAuthorizedException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws ValidationException
     * @throws \Exception
     * @throws \RuntimeException
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

        $address->setLocation(
            $this->getCityLocationCode($address->getCity())
        );
        $res = $this->addressRepository->setEntity($address)->create();
        if ($res) {
            if ($address->isMain()
            ) {
                /** @noinspection PhpParamsInspection */
                $this->updateManzanaAddress($address);
            }
            TaggedCacheHelper::clearManagedCache([
                'personal:address:' . $address->getUserId(),
            ]);
        }

        return $res;
    }

    public function disableMainItem(): void
    {
        try {
            $addresses = $this->addressRepository->findBy(
                [
                    'filter'      => [
                        'UF_USER_ID' => $this->currentUser->getCurrentUserId(),
                        'UF_MAIN'    => true,
                    ],
                    'entityClass' => Address::class,
                ]
            );
            /** @var Address $address */
            foreach ($addresses as $address) {
                $address->setMain(false);
                $this->addressRepository->setEntity($address)->update();
            }
        } catch (ObjectPropertyException|\Exception $e) {
            /** Ошибка не должна возникать */
            $logger = LoggerFactory::create('params');
            $logger->error('Ошибка снятии базового адреса доставки - ' . $e->getMessage());
        }
    }

    /**
     * @param Client  $client
     * @param Address $address
     */
    public function setClientAddress(&$client, Address $address): void
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
     * @throws ObjectPropertyException
     * @throws NotFoundException
     * @throws SecurityException
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
        /** @var Address $entity */
        $entity = $this->addressRepository->dataToEntity($data, Address::class);

        $updateEntity = $this->getById($entity->getId());
        if ($updateEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        if ($entity->getUserId() === 0) {
            $entity->setUserId($updateEntity->getUserId());
        }

        if ($entity->isMain()) {
            $this->disableMainItem();
        }

        $entity->setLocation(
            $this->getCityLocationCode($entity->getCity())
        );
        $res = $this->addressRepository->setEntity($entity)->update();
        if ($res) {
            if ($entity->isMain()
            ) {
                /** @noinspection PhpParamsInspection */
                $this->updateManzanaAddress($entity);
            }
            TaggedCacheHelper::clearManagedCache([
                'personal:address:' . $updateEntity->getUserId(),
            ]);
        }

        return $res;
    }

    /**
     * @param int $id
     *
     * @throws ObjectPropertyException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws NotAuthorizedException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @throws SecurityException
     * @throws InvalidIdentifierException
     * @throws \Exception
     * @throws BitrixRuntimeException
     * @throws ConstraintDefinitionException
     * @return bool
     */
    public function delete(int $id): bool
    {
        $deleteEntity = $this->getById($id);
        if ($deleteEntity->getUserId() !== $this->currentUser->getCurrentUserId()) {
            throw new SecurityException('не хватает прав доступа для совершения данной операции');
        }

        $res = $this->addressRepository->delete($id);
        if ($res) {
            if ($deleteEntity->isMain()) {
                /** @noinspection PhpParamsInspection */
                $this->updateManzanaAddress(new Address());
            }
            TaggedCacheHelper::clearManagedCache([
                'personal:address:' . $deleteEntity->getUserId(),
            ]);
        }

        return $res;
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
    protected function updateManzanaAddress(Address $address): void
    {
        $container = App::getInstance()->getContainer();
        /** @var ManzanaService $manzanaService */
        $manzanaService = $container->get('manzana.service');
        $client = null;
        try {
            $contactId = $manzanaService->getContactIdByUser();
            $client = new Client();
            if (!empty($contactId)) {
                $client->contactId = $contactId;
            }
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
     * @param string $city
     *
     * @return string
     * @throws \RuntimeException
     * @throws CityNotFoundException
     */
    protected function getCityLocationCode(string $city): string
    {
        $city = $this->locationService->findLocationCity($city, null, 1, true);

        return $city['CODE'];
    }
}
