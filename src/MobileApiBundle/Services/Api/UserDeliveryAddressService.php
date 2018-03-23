<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Orm\BitrixOrm;
use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\PersonalBundle\Entity\Address;

class UserDeliveryAddressService
{
    /**
     * @var CityService
     */
    private $cityService;

    /**
     * @var BitrixOrm
     */
    private $addressRepository;

    public function __construct(BitrixOrm $bitrixOrm, CityService $cityService)
    {
        $this->cityService = $cityService;
        $this->addressRepository = $bitrixOrm->getD7Repository(Address::class);
    }

    /**
     * @param int $userId
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
                $details = '';
                $parts = [
                    $address->getHousing() ? 'стр./к. ' . $address->getHousing() : '',
                    $address->getEntrance() ? 'подьезд ' . $address->getEntrance() : '',
                    $address->getFloor() ? 'этаж ' . $address->getFloor() : '',
                    $address->getIntercomCode() ? 'код домофона ' . $address->getIntercomCode() : '',
                ];
                $details .= implode(', ', array_filter($parts));

                $deliveryAddress = (new DeliveryAddress())
                    ->setId($address->getId())
                    ->setTitle($address->getName())
                    ->setStreetName($address->getStreet())
                    ->setHouse($address->getHouse())
                    ->setFlat($address->getFlat())
                    ->setDetails($details);

                if ($address->getCityLocation()) {
                    $city = $this->cityService->search('', 1, true, ['CODE' => $address->getCityLocation()])->first();
                    $deliveryAddress->setCity($city);
                }

                return $deliveryAddress;
            });
    }
}
