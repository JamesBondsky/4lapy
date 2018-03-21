<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Services\Api;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Sale\Location\DefaultSiteTable;
use Bitrix\Sale\Location\LocationTable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\LocationBundle\Exception\CityNotFoundException;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Dto\Object\City;
use FourPaws\MobileApiBundle\Exception\SystemException;
use Psr\Log\LoggerAwareInterface;

class CityService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var LocationService
     */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @param string   $query
     * @param null|int $limit
     * @param bool     $exact
     * @param array    $filter
     *
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @return City[]|Collection
     * @todo change metro check by metroways
     */
    public function search(string $query, ?int $limit = null, bool $exact = false, array $filter = []): Collection
    {
        try {
            $locations = $this->locationService->findLocation(
                $query,
                $limit,
                $exact,
                $filter
            );
        } catch (CityNotFoundException $e) {
            $locations = [];
        } catch (\Exception $e) {
            $this->log()->error($e->getMessage(), ['query' => $query]);
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
            return \in_array($city->getId(), $goodCities, true);
        });
    }

    /**
     * @throws \FourPaws\MobileApiBundle\Exception\SystemException
     * @return City[]|Collection
     */
    public function getDefaultCity()
    {
        $defaultCities = DefaultSiteTable::query()
            ->addSelect('LOCATION_CODE')
            ->setCacheTtl(604800)
            ->exec()
            ->fetchAll();
        $defaultCities = array_map(function ($city) {
            return $city['LOCATION_CODE'];
        }, $defaultCities);

        $locations = [];
        if ($defaultCities) {
            try {
                $locations = $this->locationService->findLocation(
                    '',
                    null,
                    false,
                    [
                        'CODE' => $defaultCities,
                    ]
                );
            } catch (CityNotFoundException $e) {
                dump($e);
                die();
            } catch (\Exception $e) {
                throw new SystemException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return $this->mapLocations($locations);
    }

    /**
     * @param array $locations
     * @return City[]|Collection
     */
    protected function mapLocations(array $locations): Collection
    {
        return (new ArrayCollection($locations))
            ->filter(function ($data) {
                return
                    $data &&
                    \is_array($data) &&
                    $data['NAME'] &&
                    $data['CODE'];
            })
            ->map(\Closure::fromCallable([$this, 'map']));
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
            ->setHasMetro($data['CODE'] === LocationService::LOCATION_CODE_MOSCOW);
    }
}
