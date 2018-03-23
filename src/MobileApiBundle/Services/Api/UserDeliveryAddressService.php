<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use FourPaws\MobileApiBundle\Dto\Object\DeliveryAddress;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

class UserDeliveryAddressService
{
    /**
     * @var AddressService
     */
    private $addressService;

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /**
     * @var CityService
     */
    private $cityService;

    public function __construct(
        AddressService $addressService,
        CityService $cityService
    ) {
        $this->addressService = $addressService;
        $this->cityService = $cityService;
    }

    /**
     * @param int $userId
     * @throws \Bitrix\Main\ObjectPropertyException
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function getAll(int $userId)
    {
        return $this
            ->addressService
            ->getAddressesByUser($userId)
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
