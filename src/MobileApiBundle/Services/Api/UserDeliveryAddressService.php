<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\BitrixOrmBundle\Orm\D7RepositoryInterface;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteException;
use FourPaws\MobileApiBundle\Exception\DeliveryAddressAddError;
use FourPaws\MobileApiBundle\Exception\DeliveryAddressUpdateError;
use FourPaws\MobileApiBundle\Exception\HackerException;
use FourPaws\MobileApiBundle\Exception\SystemException;
use FourPaws\PersonalBundle\Entity\Address;
use Psr\Log\LoggerAwareInterface;

class UserDeliveryAddressService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var CityService
     */
    private $cityService;

    /**
     * @var D7RepositoryInterface
     */
    private $addressRepository;

    public function __construct(BitrixOrm $bitrixOrm, CityService $cityService)
    {
        $this->cityService = $cityService;
        $this->addressRepository = $bitrixOrm->getD7Repository(Address::class);
    }

    /**
     * @param int             $userId
     * @param DeliveryAddress $deliveryAddress
     * @throws SystemException
     * @throws DeliveryAddressAddError
     */
    public function add(int $userId, DeliveryAddress $deliveryAddress): void
    {
        $address = new Address();
        $this->configureAddress($address, $deliveryAddress);
        $address->setUserId($userId);

        if (!$this->addressRepository->create($address)) {
            throw new DeliveryAddressAddError('Ошибка создания адреса');
        }
    }

    /**
     * @param int             $userId
     * @param DeliveryAddress $deliveryAddress
     * @throws \RuntimeException
     * @throws \FourPaws\MobileApiBundle\Exception\DeliveryAddressUpdateError
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @throws \FourPaws\MobileApiBundle\Exception\HackerException
     */
    public function update(int $userId, DeliveryAddress $deliveryAddress): void
    {
        $address = $this->addressRepository->findBy([
            'UF_USER_ID' => $userId,
            'ID'         => $deliveryAddress->getId(),
        ])->first();

        if (!$address) {
            $this->log()->emergency('Somebody try to hack us!', [
                'params'  => [
                    'UF_USER_ID' => $userId,
                    'ID'         => $deliveryAddress->getId(),
                ],
                'SERVER'  => $_SERVER,
                'SESSION' => $_SESSION,
            ]);
            throw new HackerException('wow. Somebody try to hack us');
        }
        /**
         * @var Address $address
         */
        $this->configureAddress($address, $deliveryAddress);
        $address->setUserId($userId);
        if (!$this->addressRepository->update($address)) {
            throw new DeliveryAddressUpdateError('Ошибка обновления адреса');
        }
    }

    /**
     * @param int $userId
     * @param int $deliveryAddressId
     * @throws \RuntimeException
     * @throws \FourPaws\MobileApiBundle\Exception\HackerException
     * @throws \FourPaws\MobileApiBundle\Dto\Request\DeliveryAddressDeleteException
     */
    public function delete(int $userId, int $deliveryAddressId): void
    {
        $count = $this->addressRepository->count([
            'UF_USER_ID' => $userId,
            'ID'         => $deliveryAddressId,
        ]);
        if (0 === $count) {
            $this->log()->emergency('Somebody try to hack us!', [
                'params'  => [
                    'UF_USER_ID' => $userId,
                    'ID'         => $deliveryAddressId,
                ],
                'SERVER'  => $_SERVER,
                'SESSION' => $_SESSION,
            ]);
            throw new HackerException('wow. Somebody try to hack us');
        }

        if (!$this->addressRepository->delete($deliveryAddressId)) {
            $this->log()->error('Ошибка удаления адреса', [
                'params' => \func_get_args(),
            ]);
            throw new DeliveryAddressDeleteException('Ошибка удаления адреса');
        }
    }

    /**
     * @param int $userId
     * @throws SystemException
     * @return Collection|DeliveryAddress[]
     */
    public function getAll(int $userId)
    {
        $addresses = $this->addressRepository->findBy(
            [
                'UF_USER_ID' => $userId,
            ],
            [
                'ID' => 'ASC',
            ]
        );
        return $addresses
            ->map(function (Address $address) {
                $deliveryAddress = (new DeliveryAddress())
                    ->setId($address->getId())
                    ->setTitle($address->getName())
                    ->setStreetName($address->getStreet())
                    ->setHouse($address->getHouse())
                    ->setFlat($address->getFlat())
                    ->setDetails($address->getDetails());

                if ($address->getCityLocation()) {
                    $city = $this->cityService->search($address->getCityLocation())->first();
                    $deliveryAddress->setCity($city);
                }

                return $deliveryAddress;
            });
    }

    /**
     * @param Address         $address
     * @param DeliveryAddress $deliveryAddress
     * @throws SystemException
     * @return Address
     */
    protected function configureAddress(Address $address, DeliveryAddress $deliveryAddress): Address
    {
        $address
            ->setName($deliveryAddress->getTitle())
            ->setStreet($deliveryAddress->getStreetName())
            ->setHouse($deliveryAddress->getHouse())
            ->setFlat($deliveryAddress->getFlat())
            ->setDetails($deliveryAddress->getDetails());

        $city = $deliveryAddress->getCity();

        if ($city) {
            $address->setCityLocation($city->getId());
            $city = $this->cityService->getCityByCode($city->getId());
            $address->setCity($city->getTitle());
        }
        return $address;
    }
}
