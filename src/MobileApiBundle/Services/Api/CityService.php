<?php

namespace FourPaws\MobileApiBundle\Services\Api;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Location\ExternalServiceTable;
use Bitrix\Sale\Location\ExternalTable;
use Closure;
use Exception;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use Psr\Log\LoggerAwareInterface;
use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Main\ObjectPropertyException;
use Doctrine\Common\Collections\Collection;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\UserBundle\Service\UserService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use Doctrine\Common\Collections\ArrayCollection;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\PersonalBundle\Service\AddressService;
use Adv\Bitrixtools\Exception\IblockNotFoundException;
use FourPaws\MobileApiBundle\Exception\SystemException;

class CityService implements LoggerAwareInterface
{
    use /** @noinspection PhpDeprecationInspection */
        LazyLoggerAwareTrait;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @var UserService
     */
    private $appUserService;

    /**
     * @var AddressService
     */
    private $addressService;


    /**
     * CityService constructor.
     *
     * @param LocationService $locationService
     * @param UserService     $appUserService
     * @param AddressService  $addressService
     */
    public function __construct(LocationService $locationService, UserService $appUserService, AddressService $addressService)
    {
        $this->locationService = $locationService;
        $this->appUserService = $appUserService;
        $this->addressService = $addressService;
    }

    /**
     * @param string   $query
     * @param null|int $limit
     * @param bool     $exact
     * @param array    $filter
     *
     * @return City[]|Collection
     * @throws SystemException
     * @todo change metro check by metroways
     */
    public function search(string $query, ?int $limit = 0, bool $exact = false, array $filter = []): Collection
    {
        try {
            /** NAME_UPPER в индексе */
            $locations = $this->locationService->findLocationNew(
                array_merge([$exact ? '=' : '?' . 'NAME.NAME_UPPER' => ToUpper($query)], $filter),
                $limit
            );
        } catch (Exception $e) {
            $this->log()->error($e->getMessage(), ['query' => $query]);
            throw new SystemException($e->getMessage(), $e->getCode(), $e);
        }
        return $this->mapLocations($locations);
    }

    /**
     * @param string $code
     *
     * @return City[]|Collection
     * @todo change metro check by metroways
     */
    public function searchByCode(string $code): Collection
    {
        try {
            $locations = [$this->locationService->findLocationByCode($code)];
        } catch (Exception $e) {
            $this->log()->error($e->getMessage(), ['code' => $code]);
            throw new SystemException($e->getMessage(), $e->getCode(), $e);
        }
        return $this->mapLocations($locations);
    }

    /**
     * @param Collection $cities
     * @param int        $minTypeId
     * @param int        $maxTypeId
     *
     * @return Collection
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function filterTypeId(Collection $cities, int $minTypeId, int $maxTypeId): Collection
    {
        if (0 === $cities->count()) {
            return $cities;
        }
        $codes = $cities
            ->map(function (City $city) {
                return $city->getId();
            })
            ->toArray();

        $goodCities = LocationTable::query()
            ->addSelect('CODE')
            ->whereIn('CODE', $codes)
            ->whereBetween('TYPE_ID', $minTypeId, $maxTypeId)
            ->setCacheTtl(3600)
            ->exec()
            ->fetchAll();

        $goodCities = array_map(function (array $data) {
            return $data['CODE'];
        }, $goodCities);

        return $cities->filter(function (City $city) use ($goodCities) {
            return in_array($city->getId(), $goodCities, true);
        });
    }

    /**
     * @return array
     * @throws IblockNotFoundException
     * @throws ObjectPropertyException
     */
    public function getDefaultCities(): array
    {
        return [
            'cities'      => $this->getDefaultCity(),
            'user_cities' => $this->getDefaultUserCity()
        ];
    }

    /**
     * @return City[]|Collection
     * @throws IblockNotFoundException
     */
    public function getDefaultCity()
    {
        $availableCities = $this->locationService->getAvailableCitiesEx();
        $locations = [];
        foreach ($availableCities as $cityGroup) {
            foreach ($cityGroup as $city) {
                try {
                    $locations[] = $this->locationService->findLocationByCode($city['CODE']);
                } catch (Exception $e) {
                    throw new SystemException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
        return $this->mapLocations($locations);
    }

    /**
     * @return Collection|City[]
     * @throws ObjectPropertyException
     */
    private function getDefaultUserCity()
    {
        $locations = [];
        try {
            $userId = $this->appUserService->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
        }
        if (!isset($userId) || !$userId) {
            return new ArrayCollection();
        }
        /** @var Address[]|ArrayCollection $addresses */
        $addresses = $this->addressService->getAddressesByUser($userId);
        /** @var Address $address */
        foreach ($addresses as $address) {
            $locations[$address->getLocation()] = $this->locationService->findLocationByCode($address->getLocation());
        };

        return $this->mapLocations(array_values($locations));
    }

    /**
     * @param string $code
     *
     * @return null|City
     * @throws SystemException
     */
    public function getCityByCode(string $code): ?City
    {
        try {
            $locations = [$this->locationService->findLocationByCode($code)];
        } catch (Exception $e) {
            throw new SystemException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->mapLocations($locations)->first() ?: null;
    }

    /**
     * @param array $locations
     *
     * @return City[]|Collection
     */
    protected function mapLocations(array $locations): Collection
    {
        return (new ArrayCollection($locations))
            ->filter(function ($data) {
                return
                    $data &&
                    is_array($data) &&
                    $data['NAME'] &&
                    $data['CODE'];
            })
            ->map(Closure::fromCallable([$this, 'map']));
    }

    /**
     * @param array $data
     *
     * @return City
     * @todo Признак метро передавать исходя из импортированных станций
     */
    protected function map(array $data): City
    {
        return (new City())
            ->setId($data['CODE'])
            ->setTitle($data['NAME'])
            ->setPath(array_map(function ($item) {
                return $item['NAME'];
            }, $data['PATH']))
            ->setHasMetro($data['CODE'] === LocationService::LOCATION_CODE_MOSCOW)
            ->setLatitude($data['LATITUDE'])
            ->setLongitude($data['LONGITUDE']);
    }

    public function getKladrIdByLocationsIds(array $locationsIds)
    {
        $results = ExternalTable::query()
            ->setSelect(['LOCATION_ID', 'XML_ID'])
            ->setFilter([
                'SERVICE.CODE' => LocationService::KLADR_SERVICE_CODE,
                '=LOCATION_ID'      => $locationsIds,
            ])
            ->registerRuntimeField(
                new ReferenceField(
                    'SERVICE',
                    ExternalServiceTable::getEntity(),
                    ['=this.SERVICE_ID' => 'ref.ID']
                )
            )
            ->exec()->fetchAll();

        $result = [];
        foreach ($results as $res) {
            $result[$res['LOCATION_ID']] = $res['XML_ID'];
        }

        return $result;
    }

    /**
     * @param array $locations
     * @return array
     */
    public function convertInDadataFormat(array $locations)
    {
        $locationsIds = array_keys($locations);
        $kladrIds = $this->getKladrIdByLocationsIds($locationsIds);

        $allowLocations = [];

        foreach ($kladrIds as $locationId => $kladrId) {
            if (isset($locations[$locationId])) {
                $locations[$locationId]['KLADR'] = $kladrId;
                $allowLocations[] = [
                    'data' => [
                        'city' => $locations[$locationId]['NAME'],
                        'region_with_type' => '',
                        'kladr_id' => $locations[$locationId]['KLADR'],
                    ],
                ];
            }
        }

        return $allowLocations;
    }
}
